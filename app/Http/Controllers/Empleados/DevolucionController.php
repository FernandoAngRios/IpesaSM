<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Devolucion;
use App\Models\DevolucionItem;
use App\Models\MovimientoCaja;
use App\Models\SucursalProducto;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Devolucion::with(['venta', 'sucursal', 'user'])->latest();

        if (! $user->isAdmin()) {
            $ids = $user->sucursalesPermitidas()->pluck('id');
            $query->whereIn('sucursal_id', $ids);
        }

        $devoluciones = $query->paginate(20)->withQueryString();

        return view('empleados.devoluciones.index', compact('devoluciones'));
    }

    public function show(Devolucion $devolucion)
    {
        abort_unless(
            auth()->user()->puedeOperarSucursal($devolucion->sucursal_id),
            403
        );

        $devolucion->load(['venta', 'sucursal', 'user', 'items']);

        return view('empleados.devoluciones.show', compact('devolucion'));
    }

    public function create(Venta $venta)
    {
        $this->authorizeVenta($venta);

        $venta->load('items.presentation', 'sucursal', 'devoluciones.items');

        // Calcula cuánto ya se devolvió por item
        $devueltosPorItem = $venta->devoluciones
            ->flatMap(fn ($d) => $d->items)
            ->groupBy('venta_item_id')
            ->map(fn ($rows) => $rows->sum('cantidad'));

        // Solo muestra items que aún tienen cantidad devolvible
        $items = $venta->items->map(function (VentaItem $item) use ($devueltosPorItem) {
            $yaDevuelto   = (float) ($devueltosPorItem[$item->id] ?? 0);
            $disponible   = max(0, (float) $item->cantidad - $yaDevuelto);
            return $item->setAttribute('cantidad_disponible', $disponible);
        })->filter(fn ($item) => $item->cantidad_disponible > 0);

        if ($items->isEmpty()) {
            return redirect()->route('empleados.ventas.index')
                ->with('warning', 'Esta venta ya fue devuelta en su totalidad.');
        }

        return view('empleados.devoluciones.create', compact('venta', 'items'));
    }

    public function store(Request $request, Venta $venta)
    {
        $this->authorizeVenta($venta);

        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|integer|exists:venta_items,id',
            'items.*.cantidad' => 'required|numeric|min:0.001',
            'motivo'         => 'nullable|string|max:255',
        ]);

        $venta->load('items.presentation', 'devoluciones.items');

        // Calcula disponible por item
        $devueltosPorItem = $venta->devoluciones
            ->flatMap(fn ($d) => $d->items)
            ->groupBy('venta_item_id')
            ->map(fn ($rows) => $rows->sum('cantidad'));

        $itemsVenta = $venta->items->keyBy('id');

        $lineas = [];
        foreach ($request->items as $linea) {
            $itemId  = (int) $linea['id'];
            $ventaItem = $itemsVenta[$itemId] ?? null;

            if (!$ventaItem) continue;

            $yaDevuelto = (float) ($devueltosPorItem[$itemId] ?? 0);
            $disponible = max(0, (float) $ventaItem->cantidad - $yaDevuelto);
            $cantidad   = min((float) $linea['cantidad'], $disponible);

            if ($cantidad <= 0) continue;

            $lineas[] = [
                'ventaItem' => $ventaItem,
                'cantidad'  => $cantidad,
            ];
        }

        if (empty($lineas)) {
            return back()->with('error', 'No hay cantidades válidas para devolver.');
        }

        DB::transaction(function () use ($venta, $lineas, $request) {
            $totalDevuelto = 0;

            $devolucion = Devolucion::create([
                'venta_id'       => $venta->id,
                'sucursal_id'    => $venta->sucursal_id,
                'user_id'        => auth()->id(),
                'motivo'         => $request->motivo,
                'total_devuelto' => 0,
            ]);

            foreach ($lineas as $linea) {
                $ventaItem = $linea['ventaItem'];
                $cantidad  = $linea['cantidad'];
                $subtotal  = round($cantidad * (float) $ventaItem->precio_unitario, 2);
                $totalDevuelto += $subtotal;

                DevolucionItem::create([
                    'devolucion_id'       => $devolucion->id,
                    'venta_item_id'       => $ventaItem->id,
                    'nombre_producto'     => $ventaItem->nombre_producto,
                    'nombre_presentacion' => $ventaItem->nombre_presentacion,
                    'cantidad'            => $cantidad,
                    'precio_unitario'     => $ventaItem->precio_unitario,
                    'subtotal'            => $subtotal,
                ]);

                // Restaurar stock si el item tiene producto y presentación registrados
                if ($ventaItem->product_id && $ventaItem->presentation) {
                    $litrosPorUnidad = (float) $ventaItem->presentation->litros;
                    $litrosARestaurar = $cantidad * $litrosPorUnidad;

                    SucursalProducto::where('sucursal_id', $venta->sucursal_id)
                        ->where('product_id', $ventaItem->product_id)
                        ->increment('stock_litros', $litrosARestaurar);
                }
            }

            $devolucion->update(['total_devuelto' => $totalDevuelto]);

            // Registrar salida en caja activa si existe
            $caja = Caja::abierta()->where('sucursal_id', $venta->sucursal_id)->first();
            if ($caja) {
                MovimientoCaja::create([
                    'caja_id'        => $caja->id,
                    'user_id'        => auth()->id(),
                    'devolucion_id'  => $devolucion->id,
                    'tipo'           => 'salida',
                    'concepto'       => 'Devolución venta #' . str_pad($venta->id, 6, '0', STR_PAD_LEFT),
                    'monto'          => $totalDevuelto,
                ]);
            }
        });

        return redirect()->route('empleados.ventas.show', $venta)
            ->with('success', 'Devolución registrada correctamente.');
    }

    private function authorizeVenta(Venta $venta): void
    {
        abort_if($venta->estado !== 'cerrada', 403, 'Solo se pueden devolver ventas cerradas.');
        abort_unless(auth()->user()->puedeOperarSucursal($venta->sucursal_id), 403, 'No tienes acceso a las devoluciones de este almacén.');
    }
}
