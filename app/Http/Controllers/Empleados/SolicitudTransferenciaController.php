<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SolicitudTransferencia;
use App\Models\SolicitudTransferenciaItem;
use App\Models\Sucursal;
use App\Models\SucursalProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudTransferenciaController extends Controller
{
    public function index(Request $request)
    {
        $user        = auth()->user();
        $sucursalIds = $user->sucursalesPermitidas()->pluck('id');

        $query = SolicitudTransferencia::with(['solicitanteSucursal', 'origenSucursal', 'user', 'items'])
            ->where(fn ($q) =>
                $q->whereIn('solicitante_sucursal_id', $sucursalIds)
                  ->orWhereIn('origen_sucursal_id', $sucursalIds)
            )
            ->latest();

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        $solicitudes = $query->paginate(20)->withQueryString();
        $estado      = $request->get('estado', 'todas');

        return view('empleados.solicitudes.index', compact('solicitudes', 'estado'));
    }

    public function create()
    {
        $user       = auth()->user();
        $misSucursal = $user->sucursalesPermitidas();
        $todas       = Sucursal::activo()->orderBy('nombre')->get();
        $products    = Product::active()->with('category')->orderBy('name')->get();

        $stocks = SucursalProducto::all()
            ->groupBy('sucursal_id')
            ->map(fn ($items) => $items->keyBy('product_id')->map->stock_litros);

        return view('empleados.solicitudes.create', compact('misSucursal', 'todas', 'products', 'stocks'));
    }

    public function store(Request $request)
    {
        $user        = auth()->user();
        $permitidas  = $user->sucursalesPermitidas()->pluck('id')->all();

        $data = $request->validate([
            'solicitante_sucursal_id' => 'required|exists:sucursales,id',
            'origen_sucursal_id'      => 'required|exists:sucursales,id|different:solicitante_sucursal_id',
            'notas_solicitud'         => 'nullable|string|max:500',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id|distinct',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
        ], [
            'items.required'              => 'Agrega al menos un producto.',
            'items.*.product_id.distinct' => 'No puedes repetir el mismo producto.',
        ]);

        if (!in_array((int) $data['solicitante_sucursal_id'], $permitidas)) {
            abort(403, 'No tienes permiso para solicitar en nombre de ese almacén.');
        }

        DB::transaction(function () use ($data, $user) {
            $solicitud = SolicitudTransferencia::create([
                'solicitante_sucursal_id' => $data['solicitante_sucursal_id'],
                'origen_sucursal_id'      => $data['origen_sucursal_id'],
                'user_id'                 => $user->id,
                'estado'                  => 'pendiente',
                'notas_solicitud'         => $data['notas_solicitud'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $solicitud->items()->create([
                    'product_id'          => $item['product_id'],
                    'cantidad_solicitada' => $item['cantidad'],
                ]);
            }
        });

        return redirect()->route('empleados.solicitudes.index')
            ->with('success', 'Solicitud enviada correctamente.');
    }

    public function show(SolicitudTransferencia $solicitud)
    {
        $this->authorizeSolicitud($solicitud);

        $solicitud->load([
            'items.product.category',
            'solicitanteSucursal',
            'origenSucursal',
            'user',
            'procesadoPor',
            'recibidoPor',
        ]);

        // Stock actual en origen, para que quien procesa vea cuánto tiene disponible
        $stockOrigen = SucursalProducto::where('sucursal_id', $solicitud->origen_sucursal_id)
            ->whereIn('product_id', $solicitud->items->pluck('product_id'))
            ->pluck('stock_litros', 'product_id');

        $user           = auth()->user();
        $puedeEnviar    = $solicitud->esPendiente()
            && ($user->isAdmin() || $user->sucursalesPermitidas()->pluck('id')->contains($solicitud->origen_sucursal_id));
        $puedeRecibir   = $solicitud->esEnviada()
            && ($user->isAdmin() || $user->sucursalesPermitidas()->pluck('id')->contains($solicitud->solicitante_sucursal_id));

        return view('empleados.solicitudes.show', compact(
            'solicitud', 'stockOrigen', 'puedeEnviar', 'puedeRecibir'
        ));
    }

    public function procesar(Request $request, SolicitudTransferencia $solicitud)
    {
        $this->authorizeSolicitud($solicitud);

        if (!$solicitud->esPendiente()) {
            return back()->with('error', 'Esta solicitud ya fue procesada.');
        }

        $user = auth()->user();
        if (!$user->isAdmin() && !$user->sucursalesPermitidas()->pluck('id')->contains($solicitud->origen_sucursal_id)) {
            abort(403, 'No tienes permiso para procesar solicitudes de ese almacén.');
        }

        $data = $request->validate([
            'notas_envio'          => 'nullable|string|max:500',
            'items'                => 'required|array',
            'items.*.id'           => 'required|exists:solicitud_transferencia_items,id',
            'items.*.cantidad_enviada' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($solicitud, $data, $user) {
            foreach ($data['items'] as $itemData) {
                $item   = $solicitud->items->find($itemData['id']);
                $enviada = (float) $itemData['cantidad_enviada'];

                if (!$item) continue;

                if ($enviada > 0) {
                    // Descontar stock del origen
                    $reg = SucursalProducto::where('sucursal_id', $solicitud->origen_sucursal_id)
                        ->where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    $disponible = $reg ? (float) $reg->stock_litros : 0;

                    if ($enviada > $disponible) {
                        throw new \RuntimeException(
                            "Stock insuficiente de «{$item->product->name}». Disponible: {$disponible} L"
                        );
                    }

                    if ($reg) {
                        $reg->decrement('stock_litros', $enviada);
                    }
                }

                $item->update(['cantidad_enviada' => $enviada]);
            }

            $solicitud->update([
                'estado'       => 'enviada',
                'notas_envio'  => $data['notas_envio'] ?? null,
                'procesado_por' => $user->id,
                'procesado_at'  => now(),
            ]);
        });

        return redirect()->route('empleados.solicitudes.show', $solicitud)
            ->with('success', 'Solicitud procesada. Stock descontado del almacén de origen.');
    }

    public function recibir(Request $request, SolicitudTransferencia $solicitud)
    {
        $this->authorizeSolicitud($solicitud);

        if (!$solicitud->esEnviada()) {
            return back()->with('error', 'Esta solicitud aún no ha sido enviada.');
        }

        $user = auth()->user();
        if (!$user->isAdmin() && !$user->sucursalesPermitidas()->pluck('id')->contains($solicitud->solicitante_sucursal_id)) {
            abort(403, 'No tienes permiso para confirmar recepciones en ese almacén.');
        }

        $data = $request->validate([
            'notas_recepcion'          => 'nullable|string|max:500',
            'items'                    => 'required|array',
            'items.*.id'               => 'required|exists:solicitud_transferencia_items,id',
            'items.*.cantidad_recibida' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($solicitud, $data, $user) {
            foreach ($data['items'] as $itemData) {
                $item     = $solicitud->items->find($itemData['id']);
                $recibida = (float) $itemData['cantidad_recibida'];

                if (!$item) continue;

                if ($recibida > 0) {
                    SucursalProducto::firstOrCreate(
                        [
                            'sucursal_id' => $solicitud->solicitante_sucursal_id,
                            'product_id'  => $item->product_id,
                        ],
                        ['stock_litros' => 0]
                    )->increment('stock_litros', $recibida);
                }

                $item->update(['cantidad_recibida' => $recibida]);
            }

            $solicitud->update([
                'estado'          => 'recibida',
                'notas_recepcion' => $data['notas_recepcion'] ?? null,
                'recibido_por'    => $user->id,
                'recibido_at'     => now(),
            ]);
        });

        return redirect()->route('empleados.solicitudes.show', $solicitud)
            ->with('success', 'Recepción confirmada. Stock actualizado.');
    }

    public function cancelar(SolicitudTransferencia $solicitud)
    {
        $this->authorizeSolicitud($solicitud);

        if (!$solicitud->esPendiente()) {
            return back()->with('error', 'Solo se pueden cancelar solicitudes pendientes.');
        }

        $solicitud->update(['estado' => 'cancelada']);

        return redirect()->route('empleados.solicitudes.index')
            ->with('success', 'Solicitud cancelada.');
    }

    private function authorizeSolicitud(SolicitudTransferencia $solicitud): void
    {
        $user        = auth()->user();
        $sucursalIds = $user->sucursalesPermitidas()->pluck('id');

        $involucrado = $sucursalIds->contains($solicitud->solicitante_sucursal_id)
            || $sucursalIds->contains($solicitud->origen_sucursal_id);

        abort_unless($user->isAdmin() || $involucrado, 403);
    }
}
