<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sucursal;
use App\Models\SucursalProducto;
use App\Models\Transferencia;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function index()
    {
        $sucursales = Sucursal::withCount('inventario')->latest()->get();
        return view('empleados.almacenes.index', compact('sucursales'));
    }

    public function create()
    {
        return view('empleados.almacenes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:100',
            'direccion' => 'required|string|max:255',
            'telefono'  => 'required|string|max:30',
            'foto'      => 'nullable|image|max:3072',
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = uniqid('alm_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/sucursales'), $filename);
            $data['foto'] = $filename;
        }

        $data['activo'] = $request->boolean('activo', true);

        Sucursal::create($data);

        return redirect()->route('empleados.almacenes.index')
            ->with('success', 'Almacén creado correctamente.');
    }

    public function show(Sucursal $sucursal)
    {
        $puedeEditar = auth()->user()->puedeOperarSucursal($sucursal->id);
        $inventario = $sucursal->inventario()
            ->with('product.category')
            ->orderByDesc('stock_litros')
            ->get();

        $transferencias = Transferencia::where('origen_id', $sucursal->id)
            ->orWhere('destino_id', $sucursal->id)
            ->with(['product', 'origen', 'destino', 'user'])
            ->latest()
            ->take(20)
            ->get();

        $products = Product::active()->with('category')->orderBy('name')->get();

        return view('empleados.almacenes.show', compact('sucursal', 'inventario', 'transferencias', 'products', 'puedeEditar'));
    }

    public function edit(Sucursal $sucursal)
    {
        return view('empleados.almacenes.edit', compact('sucursal'));
    }

    public function update(Request $request, Sucursal $sucursal)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:100',
            'direccion' => 'required|string|max:255',
            'telefono'  => 'required|string|max:30',
            'foto'      => 'nullable|image|max:3072',
        ]);

        if ($request->hasFile('foto')) {
            if ($sucursal->foto) {
                @unlink(public_path('images/sucursales/' . $sucursal->foto));
            }
            $file = $request->file('foto');
            $filename = uniqid('alm_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/sucursales'), $filename);
            $data['foto'] = $filename;
        } else {
            unset($data['foto']);
        }

        $data['activo'] = $request->boolean('activo');

        $sucursal->update($data);

        return redirect()->route('empleados.almacenes.show', $sucursal)
            ->with('success', 'Almacén actualizado correctamente.');
    }

    public function destroy(Sucursal $sucursal)
    {
        if ($sucursal->foto) {
            @unlink(public_path('images/sucursales/' . $sucursal->foto));
        }
        $sucursal->delete();

        return redirect()->route('empleados.almacenes.index')
            ->with('success', 'Almacén eliminado.');
    }

    public function ajustarStock(Request $request, Sucursal $sucursal)
    {
        if (!auth()->user()->puedeOperarSucursal($sucursal->id)) {
            abort(403, 'No tienes permiso para ajustar stock en este almacén.');
        }

        $data = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'stock_litros' => 'required|numeric|min:0',
        ]);

        SucursalProducto::updateOrCreate(
            ['sucursal_id' => $sucursal->id, 'product_id' => $data['product_id']],
            ['stock_litros' => $data['stock_litros']]
        );

        return back()->with('success', 'Stock actualizado correctamente.');
    }
}
