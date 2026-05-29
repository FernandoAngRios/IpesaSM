<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\MovimientoTinta;
use App\Models\Tinta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TintaController extends Controller
{
    public function index()
    {
        $tintas = Tinta::with(['movimientos' => fn($q) => $q->with('usuario')->latest()->limit(10)])
            ->orderBy('nombre')
            ->get();

        return view('empleados.tintas.index', compact('tintas'));
    }

    public function show(Tinta $tinta)
    {
        $movimientos = $tinta->movimientos()
            ->with('usuario')
            ->latest()
            ->paginate(25);

        return view('empleados.tintas.show', compact('tinta', 'movimientos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'       => 'required|string|max:100|unique:tintas,nombre',
            'descripcion'  => 'nullable|string|max:255',
            'color_hex'    => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'stock_minimo' => 'required|numeric|min:0',
        ]);

        Tinta::create($data);

        return back()->with('success', 'Tinta creada.');
    }

    public function update(Request $request, Tinta $tinta)
    {
        $data = $request->validate([
            'nombre'       => 'required|string|max:100|unique:tintas,nombre,' . $tinta->id,
            'descripcion'  => 'nullable|string|max:255',
            'color_hex'    => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'stock_minimo' => 'required|numeric|min:0',
            'activa'       => 'boolean',
        ]);

        $tinta->update($data);

        return back()->with('success', 'Tinta actualizada.');
    }

    public function destroy(Tinta $tinta)
    {
        if ($tinta->movimientos()->exists()) {
            return back()->with('error', 'No se puede eliminar: la tinta tiene movimientos registrados.');
        }

        $tinta->delete();
        return back()->with('success', 'Tinta eliminada.');
    }

    public function movimiento(Request $request, Tinta $tinta)
    {
        $data = $request->validate([
            'tipo'           => 'required|in:entrada,uso,ajuste',
            'cantidad_litros'=> 'required|numeric|min:0.001',
            'referencia'     => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($tinta, $data) {
            MovimientoTinta::create([
                'tinta_id'        => $tinta->id,
                'usuario_id'      => auth()->id(),
                'tipo'            => $data['tipo'],
                'cantidad_litros' => $data['cantidad_litros'],
                'referencia'      => $data['referencia'] ?? null,
            ]);

            if ($data['tipo'] === 'entrada') {
                $tinta->increment('stock_litros', $data['cantidad_litros']);
            } elseif ($data['tipo'] === 'uso') {
                $tinta->decrement('stock_litros', $data['cantidad_litros']);
            } else {
                // ajuste = conteo físico, establece el stock al valor exacto
                $tinta->update(['stock_litros' => $data['cantidad_litros']]);
            }
        });

        return back()->with('success', 'Movimiento registrado.');
    }
}
