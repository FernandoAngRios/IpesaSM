<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CajaController extends Controller
{
    public function index(Request $request)
    {
        $user       = auth()->user();
        $sucursales = $user->sucursalesPermitidas();
        $sucursalIds = $sucursales->pluck('id');

        // Filtro por almacén (aplica a abiertas y al historial)
        $filtroSucursalId = $request->filled('sucursal_id') ? (int) $request->sucursal_id : null;
        $idsParaFiltrar   = $filtroSucursalId ? collect([$filtroSucursalId]) : $sucursalIds;

        $abiertas = Caja::abierta()
            ->whereIn('sucursal_id', $idsParaFiltrar)
            ->with(['sucursal', 'user'])
            ->latest()
            ->get();

        $cerradas = Caja::cerrada()
            ->whereIn('sucursal_id', $idsParaFiltrar)
            ->with(['sucursal', 'user', 'cerradoPor', 'movimientos'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $sucursalesSinCaja = $sucursales->filter(
            fn ($s) => !$abiertas->contains('sucursal_id', $s->id)
        );

        return view('empleados.caja.index', compact('abiertas', 'cerradas', 'sucursales', 'sucursalesSinCaja'));
    }

    public function abrir(Request $request)
    {
        $user      = auth()->user();
        $permitidas = $user->sucursalesPermitidas()->pluck('id')->all();

        $data = $request->validate([
            'sucursal_id'   => ['required', 'exists:sucursales,id', Rule::in($permitidas)],
            'saldo_inicial' => 'required|numeric|min:0',
        ], [
            'sucursal_id.in' => 'No tienes permiso para abrir caja en ese almacén.',
        ]);

        $existente = Caja::abierta()->where('sucursal_id', $data['sucursal_id'])->exists();
        if ($existente) {
            return back()->withErrors(['sucursal_id' => 'Ya existe una caja abierta para este almacén.']);
        }

        $caja = Caja::create([
            'sucursal_id'   => $data['sucursal_id'],
            'user_id'       => $user->id,
            'saldo_inicial' => $data['saldo_inicial'],
        ]);

        return redirect()->route('empleados.caja.show', $caja)
            ->with('success', 'Caja abierta correctamente.');
    }

    public function imprimir(Caja $caja)
    {
        $user = auth()->user();
        abort_unless($user->isAdmin() || $user->puedeOperarSucursal($caja->sucursal_id), 403);

        $caja->load(['sucursal', 'user', 'cerradoPor', 'movimientos.venta.pagos', 'movimientos.devolucion']);

        $ventasIds = $caja->movimientos->whereNotNull('venta_id')->pluck('venta_id')->unique();
        $pagosPorTipo = \App\Models\VentaPago::whereIn('venta_id', $ventasIds)
            ->selectRaw('tipo, SUM(monto) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $movsDevoluciones = $caja->movimientos->filter(fn ($m) => !is_null($m->devolucion_id));

        return view('empleados.caja.imprimir', compact('caja', 'pagosPorTipo', 'movsDevoluciones'));
    }

    public function verCierre(Caja $caja)
    {
        abort_if($caja->estado !== 'abierta', 403, 'Esta caja ya está cerrada.');
        $user = auth()->user();
        abort_unless($user->isAdmin() || $user->puedeOperarSucursal($caja->sucursal_id), 403);

        $caja->load(['sucursal', 'user', 'movimientos.venta.pagos', 'movimientos.devolucion']);

        $ventasIds = $caja->movimientos->whereNotNull('venta_id')->pluck('venta_id')->unique();
        $pagosPorTipo = \App\Models\VentaPago::whereIn('venta_id', $ventasIds)
            ->selectRaw('tipo, SUM(monto) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $movsDevoluciones = $caja->movimientos->filter(fn ($m) => !is_null($m->devolucion_id));

        return view('empleados.caja.cierre', compact('caja', 'pagosPorTipo', 'movsDevoluciones'));
    }

    public function show(Caja $caja)
    {
        $user = auth()->user();
        abort_unless($user->isAdmin() || $user->puedeOperarSucursal($caja->sucursal_id), 403);

        $caja->load(['sucursal', 'user', 'cerradoPor', 'movimientos.user', 'movimientos.venta', 'movimientos.devolucion']);

        return view('empleados.caja.show', compact('caja'));
    }

    public function movimiento(Request $request, Caja $caja)
    {
        abort_unless($caja->estado === 'abierta', 403, 'Esta caja ya está cerrada.');
        $user = auth()->user();
        abort_unless($user->isAdmin() || $user->puedeOperarSucursal($caja->sucursal_id), 403);

        $data = $request->validate([
            'tipo'     => 'required|in:entrada,salida',
            'concepto' => 'required|string|max:200',
            'monto'    => 'required|numeric|min:0.01',
        ]);

        MovimientoCaja::create([
            'caja_id'  => $caja->id,
            'user_id'  => $user->id,
            'tipo'     => $data['tipo'],
            'concepto' => $data['concepto'],
            'monto'    => $data['monto'],
        ]);

        return back()->with('success', 'Movimiento registrado.');
    }

    public function cerrar(Request $request, Caja $caja)
    {
        abort_unless($caja->estado === 'abierta', 403, 'Esta caja ya está cerrada.');
        $user = auth()->user();
        abort_unless($user->isAdmin() || $user->puedeOperarSucursal($caja->sucursal_id), 403);

        $data = $request->validate([
            'saldo_final' => 'required|numeric|min:0',
        ]);

        $caja->update([
            'saldo_final' => $data['saldo_final'],
            'estado'      => 'cerrada',
            'cerrado_por' => $user->id,
            'cerrada_at'  => now(),
        ]);

        return redirect()->route('empleados.caja.imprimir', $caja)
            ->with('success', 'Caja cerrada correctamente.');
    }

}
