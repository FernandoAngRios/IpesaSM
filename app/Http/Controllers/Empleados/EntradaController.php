<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Entrada;
use App\Models\Product;
use App\Models\Sucursal;
use App\Models\SucursalProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EntradaController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Entrada::with(['sucursal', 'product', 'user'])->latest();

        if ($user->isAdmin()) {
            $sucursales = Sucursal::activo()->orderBy('nombre')->get();
            if ($request->filled('sucursal_id')) {
                $query->where('sucursal_id', $request->sucursal_id);
            }
        } else {
            $sucursales  = $user->sucursales()->where('activo', true)->orderBy('nombre')->get();
            $query->whereIn('sucursal_id', $sucursales->pluck('id'));
        }

        $entradas = $query->paginate(20)->withQueryString();

        return view('empleados.entradas.index', compact('entradas', 'sucursales'));
    }

    public function create()
    {
        $sucursales   = auth()->user()->sucursalesPermitidas();
        $products     = Product::active()->with('category')->orderBy('name')->get();
        $productUnits = $products->pluck('unit', 'id');

        return view('empleados.entradas.create', compact('sucursales', 'products', 'productUnits'));
    }

    public function store(Request $request)
    {
        $permitidas = auth()->user()->sucursalesPermitidas()->pluck('id')->all();

        $data = $request->validate([
            'sucursal_id'      => ['required', 'exists:sucursales,id', Rule::in($permitidas)],
            'product_id'       => 'required|exists:products,id',
            'cantidad_litros'  => 'required|numeric|min:0.001',
            'proveedor_nombre' => 'required|string|max:150',
            'nota'             => 'nullable|string|max:500',
        ], [
            'sucursal_id.in' => 'No tienes permiso para registrar entradas en ese almacén.',
        ]);

        DB::transaction(function () use ($data) {
            $reg = SucursalProducto::firstOrCreate(
                ['sucursal_id' => $data['sucursal_id'], 'product_id' => $data['product_id']],
                ['stock_litros' => 0]
            );
            $reg->increment('stock_litros', $data['cantidad_litros']);

            Entrada::create([
                'sucursal_id'      => $data['sucursal_id'],
                'product_id'       => $data['product_id'],
                'cantidad_litros'  => $data['cantidad_litros'],
                'proveedor_nombre' => $data['proveedor_nombre'],
                'nota'             => $data['nota'] ?? null,
                'user_id'          => auth()->id(),
            ]);
        });

        return redirect()->route('empleados.entradas.index')
            ->with('success', 'Entrada de material registrada correctamente.');
    }

    public function show(Entrada $entrada)
    {
        $entrada->load(['sucursal', 'product.category', 'user']);
        return view('empleados.entradas.show', compact('entrada'));
    }

    public function edit(Entrada $entrada)
    {
        $entrada->load(['sucursal', 'product.category', 'user']);
        return view('empleados.entradas.edit', compact('entrada'));
    }

    public function update(Request $request, Entrada $entrada)
    {
        $data = $request->validate([
            'cantidad_litros'  => 'required|numeric|min:0.001',
            'proveedor_nombre' => 'required|string|max:150',
            'nota'             => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($entrada, $data) {
            $delta = round((float) $data['cantidad_litros'] - (float) $entrada->cantidad_litros, 6);

            if (abs($delta) > 0.0001) {
                SucursalProducto::where('sucursal_id', $entrada->sucursal_id)
                    ->where('product_id', $entrada->product_id)
                    ->increment('stock_litros', $delta);
            }

            $entrada->update($data);
        });

        return redirect()->route('empleados.entradas.show', $entrada)
            ->with('success', 'Entrada actualizada. El stock fue ajustado correctamente.');
    }

    public function destroy(Entrada $entrada)
    {
        DB::transaction(function () use ($entrada) {
            SucursalProducto::where('sucursal_id', $entrada->sucursal_id)
                ->where('product_id', $entrada->product_id)
                ->decrement('stock_litros', $entrada->cantidad_litros);

            $entrada->delete();
        });

        return redirect()->route('empleados.entradas.index')
            ->with('success', 'Entrada eliminada y stock revertido.');
    }
}
