<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sucursal;
use App\Models\SucursalProducto;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\VentaPago;
use App\Models\Vendedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PosController extends Controller
{
    // ── Interfaz principal ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user       = auth()->user();
        $sucursales = $user->sucursalesPermitidas();

        $tickets = Venta::abierta()
            ->where('user_id', $user->id)
            ->with(['sucursal', 'items'])
            ->oldest()
            ->get();

        $activeId = (int) $request->get('ticket');
        $active   = $tickets->firstWhere('id', $activeId) ?? $tickets->first();

        if ($active) {
            $active->load('items.product.presentations', 'items.presentation');
        }

        $cajaActiva = $active
            ? Caja::abierta()->where('sucursal_id', $active->sucursal_id)->first()
            : null;

        $vendedores = Vendedor::activo()->orderBy('nombre')->get();

        return view('empleados.pos.index', compact('sucursales', 'tickets', 'active', 'cajaActiva', 'vendedores'));
    }

    // ── Gestión de tickets ─────────────────────────────────────────────────

    public function store(Request $request)
    {
        $user        = auth()->user();
        $permitidas  = $user->sucursalesPermitidas()->pluck('id')->all();

        $data = $request->validate([
            'sucursal_id' => ['required', 'exists:sucursales,id', Rule::in($permitidas)],
        ], [
            'sucursal_id.in' => 'No tienes permiso para abrir tickets en ese almacén.',
        ]);

        $venta = Venta::create([
            'sucursal_id' => $data['sucursal_id'],
            'user_id'     => $user->id,
            'estado'      => 'abierta',
            'total'       => 0,
        ]);

        return redirect()->route('empleados.pos.index', ['ticket' => $venta->id]);
    }

    public function cancelar(Venta $venta)
    {
        $this->authorizeTicket($venta);

        $venta->update(['estado' => 'cancelada']);

        $siguiente = Venta::abierta()->where('user_id', auth()->id())->latest()->first();

        return redirect()->route('empleados.pos.index', $siguiente ? ['ticket' => $siguiente->id] : []);
    }

    // ── Items (AJAX) ───────────────────────────────────────────────────────

    public function addItem(Request $request, Venta $venta): JsonResponse
    {
        $this->authorizeTicket($venta);

        $data = $request->validate([
            'product_id'              => 'required|exists:products,id',
            'product_presentation_id' => 'nullable|exists:product_presentations,id',
            'cantidad'                => 'required|numeric|min:0.001',
            'codigo_color'            => 'nullable|string|max:100',
        ]);

        $product        = Product::with('presentations')->findOrFail($data['product_id']);
        $presentationId = $data['product_presentation_id'] ?? null;
        $presentation   = $presentationId
            ? $product->presentations->find($presentationId)
            : null;

        $precio          = $presentation ? (float) $presentation->precio : (float) $product->price;
        $litrosPorUnidad = $presentation ? (float) $presentation->litros : 1.0;
        $litrosNuevos    = $litrosPorUnidad * (float) $data['cantidad'];

        // Stock disponible en el almacén de la venta
        $stockDisponible = (float) (SucursalProducto::where('sucursal_id', $venta->sucursal_id)
            ->where('product_id', $product->id)
            ->value('stock_litros') ?? 0);

        // Litros ya comprometidos por otros ítems del mismo producto en este ticket
        $litrosEnTicket = $this->litrosEnTicket($venta, $product->id);

        if (($litrosEnTicket + $litrosNuevos) > $stockDisponible) {
            $disponibleUnidades = $litrosPorUnidad > 0
                ? floor(($stockDisponible - $litrosEnTicket) / $litrosPorUnidad * 1000) / 1000
                : 0;
            return response()->json([
                'error' => "Stock insuficiente. Máximo disponible: {$disponibleUnidades}",
            ], 422);
        }

        $codigoColor = trim($data['codigo_color'] ?? '') ?: null;

        // Acumular si ya existe el mismo producto+presentación (sin código de color)
        $item = !$codigoColor
            ? $venta->items()
                ->where('product_id', $product->id)
                ->where('product_presentation_id', $presentationId)
                ->whereNull('codigo_color')
                ->first()
            : null;

        if ($item) {
            $nuevaCantidad = (float) $item->cantidad + (float) $data['cantidad'];
            $item->update([
                'cantidad' => $nuevaCantidad,
                'subtotal' => round($precio * $nuevaCantidad, 2),
            ]);
        } else {
            $venta->items()->create([
                'product_id'              => $product->id,
                'product_presentation_id' => $presentationId,
                'nombre_producto'         => $product->name,
                'nombre_presentacion'     => $presentation?->nombre,
                'codigo_color'            => $codigoColor,
                'precio_unitario'         => $precio,
                'cantidad'                => $data['cantidad'],
                'subtotal'                => round($precio * (float) $data['cantidad'], 2),
            ]);
        }

        $this->recalcTotal($venta);

        return $this->ticketJson($venta);
    }

    public function addLibre(Request $request, Venta $venta): JsonResponse
    {
        $this->authorizeTicket($venta);
        abort_if($venta->estado !== 'abierta', 403);

        $data = $request->validate([
            'nombre'   => 'required|string|max:200',
            'precio'   => 'required|numeric|min:0.01',
            'cantidad' => 'required|numeric|min:0.001',
        ]);

        $precio   = round((float) $data['precio'], 2);
        $cantidad = round((float) $data['cantidad'], 3);

        $venta->items()->create([
            'product_id'      => null,
            'nombre_producto' => trim($data['nombre']),
            'precio_unitario' => $precio,
            'cantidad'        => $cantidad,
            'subtotal'        => round($precio * $cantidad, 2),
        ]);

        $this->recalcTotal($venta);

        return $this->ticketJson($venta);
    }

    public function updateItem(Request $request, Venta $venta, VentaItem $item): JsonResponse
    {
        $this->authorizeTicket($venta);
        abort_unless($item->venta_id === $venta->id, 404);

        $data = $request->validate([
            'cantidad'     => 'nullable|numeric|min:0.001',
            'codigo_color' => 'nullable|string|max:100',
        ]);

        if ($item->product_id !== null) {
            $presentation    = $item->product_presentation_id
                ? $item->load('presentation')->presentation
                : null;
            $litrosPorUnidad = $presentation ? (float) $presentation->litros : 1.0;
            $litrosNuevos    = $litrosPorUnidad * (float) $data['cantidad'];

            $stockDisponible = (float) (SucursalProducto::where('sucursal_id', $venta->sucursal_id)
                ->where('product_id', $item->product_id)
                ->value('stock_litros') ?? 0);

            $litrosOtros = $this->litrosEnTicket($venta, $item->product_id, $item->id);

            if (($litrosOtros + $litrosNuevos) > $stockDisponible) {
                $maxUnidades = $litrosPorUnidad > 0
                    ? floor(($stockDisponible - $litrosOtros) / $litrosPorUnidad * 1000) / 1000
                    : 0;
                return response()->json([
                    'error' => "Stock insuficiente. Máximo disponible: {$maxUnidades}",
                ], 422);
            }
        }

        $updates = [];

        if (isset($data['cantidad'])) {
            $updates['cantidad'] = $data['cantidad'];
            $updates['subtotal'] = round((float) $item->precio_unitario * (float) $data['cantidad'], 2);
        }

        if (array_key_exists('codigo_color', $data)) {
            $updates['codigo_color'] = trim($data['codigo_color'] ?? '') ?: null;
        }

        $item->update($updates);

        $this->recalcTotal($venta);

        return $this->ticketJson($venta);
    }

    public function removeItem(Venta $venta, VentaItem $item): JsonResponse
    {
        $this->authorizeTicket($venta);
        abort_unless($item->venta_id === $venta->id, 404);

        $item->delete();
        $this->recalcTotal($venta);

        return $this->ticketJson($venta);
    }

    // ── Confirmar venta ────────────────────────────────────────────────────

    public function confirmar(Request $request, Venta $venta)
    {
        $this->authorizeTicket($venta);

        $data = $request->validate([
            'cliente_nombre'   => 'nullable|string|max:150',
            'cliente_telefono' => 'nullable|string|max:20',
            'vendedor'         => 'nullable|string|max:100',
            'descuento_tipo'   => 'nullable|in:porcentaje,fijo',
            'descuento_valor'  => 'nullable|numeric|min:0',
            'pagos'            => 'required|array|min:1',
            'pagos.*.tipo'     => 'required|in:efectivo,tarjeta,transferencia',
            'pagos.*.monto'    => 'required|numeric|min:0.01',
            'pagos.*.referencia' => 'nullable|string|max:100',
        ]);

        if ($venta->items->isEmpty()) {
            return back()->withErrors(['items' => 'El ticket no tiene productos.']);
        }

        // Calcular descuento y total final
        $subtotal        = (float) $venta->items()->sum('subtotal');
        $descuentoTipo   = $data['descuento_tipo']  ?? null;
        $descuentoValor  = (float) ($data['descuento_valor'] ?? 0);

        $descuentoMonto = match(true) {
            $descuentoTipo === 'porcentaje' && $descuentoValor > 0
                => round($subtotal * min($descuentoValor, 100) / 100, 2),
            $descuentoTipo === 'fijo' && $descuentoValor > 0
                => min(round($descuentoValor, 2), $subtotal),
            default => 0.0,
        };

        $totalFinal = max(0, round($subtotal - $descuentoMonto, 2));

        $totalPagado = collect($data['pagos'])->sum('monto');
        if ($totalPagado < $totalFinal) {
            return back()->withErrors(['pagos' => 'El monto pagado no cubre el total de la venta.']);
        }

        // Cargar ítems con presentaciones para calcular litros
        $venta->load('items.presentation');

        DB::transaction(function () use ($venta, $data, $descuentoMonto, $totalFinal) {
            // Validar y descontar stock por cada ítem (los libres no tienen stock)
            foreach ($venta->items as $item) {
                if ($item->product_id === null) continue;

                $litrosPorUnidad = $item->presentation ? (float) $item->presentation->litros : 1.0;
                $litros          = $litrosPorUnidad * (float) $item->cantidad;

                $reg = SucursalProducto::where('sucursal_id', $venta->sucursal_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                $stockActual = $reg ? (float) $reg->stock_litros : 0;

                if ($stockActual < $litros) {
                    throw new \RuntimeException(
                        "Stock insuficiente para «{$item->nombre_producto}». Disponible: {$stockActual} L"
                    );
                }

                if ($reg) {
                    $reg->decrement('stock_litros', $litros);
                }
            }

            // Registrar pagos
            foreach ($data['pagos'] as $pago) {
                VentaPago::create([
                    'venta_id'   => $venta->id,
                    'tipo'       => $pago['tipo'],
                    'monto'      => $pago['monto'],
                    'referencia' => $pago['referencia'] ?? null,
                ]);
            }

            // Cerrar venta (aplicar descuento al total final)
            $venta->update([
                'estado'           => 'cerrada',
                'cliente_nombre'   => $data['cliente_nombre'] ?? null,
                'cliente_telefono' => $data['cliente_telefono'] ?? null,
                'vendedor'         => $data['vendedor'] ?? null,
                'descuento'        => $descuentoMonto,
                'total'            => $totalFinal,
            ]);

            // Registrar en caja activa el efectivo recibido
            $efectivo = collect($data['pagos'])
                ->filter(fn($p) => $p['tipo'] === 'efectivo')
                ->sum('monto');

            if ($efectivo > 0) {
                $caja = Caja::abierta()
                    ->where('sucursal_id', $venta->sucursal_id)
                    ->first();

                if ($caja) {
                    MovimientoCaja::create([
                        'caja_id'  => $caja->id,
                        'user_id'  => auth()->id(),
                        'venta_id' => $venta->id,
                        'tipo'     => 'entrada',
                        'concepto' => 'Venta #' . str_pad($venta->id, 6, '0', STR_PAD_LEFT),
                        'monto'    => $efectivo,
                    ]);
                }
            }
        });

        return redirect()->route('empleados.pos.ticket', $venta);
    }

    // ── Ticket de impresión ────────────────────────────────────────────────

    public function ticket(Venta $venta)
    {
        $venta->load(['sucursal', 'user', 'items.product', 'items.presentation', 'pagos']);
        return view('empleados.pos.ticket', compact('venta'));
    }

    public function cotizacion(Venta $venta)
    {
        $venta->load(['sucursal', 'user', 'items.product', 'items.presentation']);
        return view('empleados.pos.cotizacion', compact('venta'));
    }

    public function movimientoCaja(Request $request, Venta $venta)
    {
        $data = $request->validate([
            'tipo'     => 'required|in:entrada,salida',
            'concepto' => 'required|string|max:200',
            'monto'    => 'required|numeric|min:0.01',
        ]);

        $caja = Caja::abierta()->where('sucursal_id', $venta->sucursal_id)->first();

        if (!$caja) {
            return back()->withErrors(['No hay caja abierta para este almacén.']);
        }

        MovimientoCaja::create([
            'caja_id'  => $caja->id,
            'user_id'  => auth()->id(),
            'tipo'     => $data['tipo'],
            'concepto' => $data['concepto'],
            'monto'    => $data['monto'],
        ]);

        return redirect()->route('empleados.pos.index', ['ticket' => $venta->id])
            ->with('success', ucfirst($data['tipo']) . ' registrada correctamente.');
    }

    // ── Búsqueda de productos (AJAX) ───────────────────────────────────────

    public function buscar(Request $request): JsonResponse
    {
        $q          = trim($request->get('q', ''));
        $sucursalId = (int) $request->get('sucursal_id');

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $products = Product::active()
            ->with(['category', 'presentations'])
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('codigo_barras', $q); // barcode: exact match
            })
            ->orderBy('name')
            ->limit(12)
            ->get();

        $productIds = $products->pluck('id');

        // Stock físico en el almacén
        $stocks = $sucursalId
            ? SucursalProducto::where('sucursal_id', $sucursalId)
                ->whereIn('product_id', $productIds)
                ->pluck('stock_litros', 'product_id')
            : collect();

        // Litros ya comprometidos en tickets abiertos del mismo almacén
        $comprometidos = $sucursalId
            ? VentaItem::whereHas('venta', fn ($q) =>
                $q->where('sucursal_id', $sucursalId)->where('estado', 'abierta')
              )
              ->whereIn('product_id', $productIds)
              ->whereNotNull('product_id')
              ->with('presentation')
              ->get()
              ->groupBy('product_id')
              ->map(fn ($items) => $items->sum(function (VentaItem $i) {
                  $litros = $i->presentation ? (float) $i->presentation->litros : 1.0;
                  return $litros * (float) $i->cantidad;
              }))
            : collect();

        $result = $products->map(fn ($p) => [
            'id'            => $p->id,
            'name'          => $p->name,
            'codigo_barras' => $p->codigo_barras,
            'unit'          => $p->unit,
            'price'         => (float) $p->price,
            'stock_litros'  => max(0, round((float) ($stocks[$p->id] ?? 0) - (float) ($comprometidos[$p->id] ?? 0), 3)),
            'category'      => $p->category->name,
            'color'         => $p->category->color,
            'presentations' => $p->presentations->map(fn ($pr) => [
                'id'     => $pr->id,
                'nombre' => $pr->nombre,
                'litros' => (float) $pr->litros,
                'precio' => (float) $pr->precio,
            ]),
        ]);

        return response()->json($result);
    }

    // ── Historial de ventas ────────────────────────────────────────────────

    public function historial(Request $request)
    {
        $user       = auth()->user();
        $sucursales = $user->sucursalesPermitidas();

        $query = Venta::cerrada()
            ->with(['sucursal', 'user', 'pagos', 'devoluciones'])
            ->whereIn('sucursal_id', $sucursales->pluck('id'))
            ->latest();

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        $ventas = $query->paginate(20)->withQueryString();

        return view('empleados.pos.historial', compact('ventas', 'sucursales'));
    }

    public function show(Venta $venta)
    {
        abort_unless(auth()->user()->puedeOperarSucursal($venta->sucursal_id), 403);

        $venta->load('items.presentation', 'pagos', 'sucursal', 'user', 'devoluciones.items');

        return view('empleados.pos.show', compact('venta'));
    }

    // ── Helpers privados ───────────────────────────────────────────────────

    private function authorizeTicket(Venta $venta): void
    {
        abort_unless($venta->estado === 'abierta', 403, 'Este ticket ya no está abierto.');
        abort_unless(
            auth()->user()->isAdmin() || $venta->user_id === auth()->id(),
            403
        );
    }

    private function recalcTotal(Venta $venta): void
    {
        $venta->update(['total' => $venta->items()->sum('subtotal')]);
    }

    private function litrosEnTicket(Venta $venta, int $productId, ?int $excludeItemId = null): float
    {
        return (float) $venta->items()
            ->where('product_id', $productId)
            ->when($excludeItemId, fn($q) => $q->where('id', '!=', $excludeItemId))
            ->with('presentation')
            ->get()
            ->sum(function (VentaItem $item) {
                $litros = $item->presentation ? (float) $item->presentation->litros : 1.0;
                return $litros * (float) $item->cantidad;
            });
    }

    private function ticketJson(Venta $venta): JsonResponse
    {
        $venta->load('items.product', 'items.presentation');

        return response()->json([
            'total' => (float) $venta->fresh()->total,
            'items' => $venta->items->map(fn ($i) => [
                'id'                  => $i->id,
                'nombre_producto'     => $i->nombre_producto,
                'nombre_presentacion' => $i->nombre_presentacion,
                'codigo_color'        => $i->codigo_color,
                'precio_unitario'     => (float) $i->precio_unitario,
                'cantidad'            => (float) $i->cantidad,
                'subtotal'            => (float) $i->subtotal,
            ]),
        ]);
    }

}
