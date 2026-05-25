<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sucursal;
use App\Models\SucursalProducto;
use App\Models\Transferencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransferenciaController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Transferencia::with(['product', 'origen', 'destino', 'user'])->latest();

        if ($user->isAdmin()) {
            $sucursales = Sucursal::activo()->orderBy('nombre')->get();
            if ($request->filled('sucursal_id')) {
                $id = (int) $request->sucursal_id;
                $query->where(fn ($q) => $q->where('origen_id', $id)->orWhere('destino_id', $id));
            }
        } else {
            $sucursales  = $user->sucursales()->where('activo', true)->orderBy('nombre')->get();
            $sucursalIds = $sucursales->pluck('id');
            $query->where(fn ($q) => $q->whereIn('origen_id', $sucursalIds)->orWhereIn('destino_id', $sucursalIds));
        }

        $transferencias = $query->paginate(20)->withQueryString();

        return view('empleados.transferencias.index', compact('transferencias', 'sucursales'));
    }

    public function create()
    {
        $user = auth()->user();

        $origenSucursales  = $user->sucursalesPermitidas();
        $destinoSucursales = Sucursal::activo()->orderBy('nombre')->get();
        $products          = Product::active()->with('category')->orderBy('name')->get();

        $stocks = SucursalProducto::all()
            ->groupBy('sucursal_id')
            ->map(fn ($items) => $items->keyBy('product_id')->map->stock_litros);

        $productUnits = $products->pluck('unit', 'id');

        return view('empleados.transferencias.create', compact(
            'origenSucursales', 'destinoSucursales', 'products', 'stocks', 'productUnits'
        ));
    }

    public function store(Request $request)
    {
        $origenesPermitidos = auth()->user()->sucursalesPermitidas()->pluck('id')->all();

        $data = $request->validate([
            'origen_id'       => ['required', 'exists:sucursales,id', Rule::in($origenesPermitidos)],
            'destino_id'      => 'required|exists:sucursales,id|different:origen_id',
            'product_id'      => 'required|exists:products,id',
            'cantidad_litros' => 'required|numeric|min:0.001',
            'nota'            => 'nullable|string|max:500',
        ], [
            'origen_id.in' => 'No tienes permiso para transferir desde ese almacén.',
        ]);

        $stockOrigen = SucursalProducto::where('sucursal_id', $data['origen_id'])
            ->where('product_id', $data['product_id'])
            ->value('stock_litros') ?? 0;

        if ($stockOrigen < $data['cantidad_litros']) {
            return back()
                ->withErrors(['cantidad_litros' => "Stock insuficiente en el almacén de origen. Disponible: {$stockOrigen} L"])
                ->withInput();
        }

        DB::transaction(function () use ($data) {
            SucursalProducto::where('sucursal_id', $data['origen_id'])
                ->where('product_id', $data['product_id'])
                ->decrement('stock_litros', $data['cantidad_litros']);

            Transferencia::create([
                'origen_id'       => $data['origen_id'],
                'destino_id'      => $data['destino_id'],
                'product_id'      => $data['product_id'],
                'cantidad_litros' => $data['cantidad_litros'],
                'nota'            => $data['nota'] ?? null,
                'user_id'         => auth()->id(),
                'estado'          => 'pendiente',
            ]);
        });

        return redirect()->route('empleados.transferencias.index')
            ->with('success', 'Transferencia registrada correctamente.');
    }

    public function show(Transferencia $transferencia)
    {
        $transferencia->load(['product', 'origen', 'destino', 'user', 'confirmadoPor']);
        return view('empleados.transferencias.show', compact('transferencia'));
    }

    public function confirmar(Request $request, Transferencia $transferencia)
    {
        if ($transferencia->estado === 'confirmada') {
            return back()->with('error', 'Esta transferencia ya fue confirmada.');
        }

        if (!auth()->user()->puedeOperarSucursal($transferencia->destino_id)) {
            abort(403, 'No tienes permiso para confirmar recepciones en ese almacén.');
        }

        $data = $request->validate([
            'cantidad_recibida' => 'required|numeric|min:0.001|max:' . $transferencia->cantidad_litros,
            'nota_recepcion'    => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($transferencia, $data) {
            $destReg = SucursalProducto::firstOrCreate(
                ['sucursal_id' => $transferencia->destino_id, 'product_id' => $transferencia->product_id],
                ['stock_litros' => 0]
            );
            $destReg->increment('stock_litros', $data['cantidad_recibida']);

            $transferencia->update([
                'estado'            => 'confirmada',
                'cantidad_recibida' => $data['cantidad_recibida'],
                'confirmado_por'    => auth()->id(),
                'confirmado_at'     => now(),
                'nota_recepcion'    => $data['nota_recepcion'] ?? null,
            ]);
        });

        return redirect()->route('empleados.transferencias.show', $transferencia)
            ->with('success', 'Transferencia confirmada. Stock actualizado en destino.');
    }
}
