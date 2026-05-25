<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Vendedor;
use Illuminate\Http\Request;

class VendedorController extends Controller
{
    public function index()
    {
        $vendedores = Vendedor::orderBy('nombre')->get();
        return view('empleados.vendedores.index', compact('vendedores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:vendedores,nombre',
        ]);

        Vendedor::create(['nombre' => trim($data['nombre']), 'activo' => true]);

        return back()->with('success', 'Vendedor agregado.');
    }

    public function update(Request $request, Vendedor $vendedor)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:vendedores,nombre,' . $vendedor->id,
            'activo' => 'boolean',
        ]);

        $vendedor->update([
            'nombre' => trim($data['nombre']),
            'activo' => $request->boolean('activo'),
        ]);

        return back()->with('success', 'Vendedor actualizado.');
    }

    public function destroy(Vendedor $vendedor)
    {
        $vendedor->delete();
        return back()->with('success', 'Vendedor eliminado.');
    }
}
