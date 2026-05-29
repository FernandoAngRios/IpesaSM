@extends('layouts.empleados')

@section('title', 'Cierre de caja — ' . $caja->sucursal->nombre)
@section('page-title', 'Cierre de caja')

@section('content')
@php
    $efectivoVentas   = $caja->totalVentas();
    $entradasManuales = $caja->totalEntradasManuales();
    $salidas          = $caja->totalSalidas();
    $saldoEsperado    = $caja->saldoActual();
    $totalVentasDia   = $pagosPorTipo->sum();
@endphp

<div class="max-w-2xl mx-auto space-y-6"
     x-data="{ contado: {{ number_format($saldoEsperado, 2, '.', '') }} }">

    {{-- Volver --}}
    <a href="{{ route('empleados.caja.show', $caja) }}"
       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Volver a caja
    </a>

    {{-- Encabezado --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 flex items-center justify-between gap-4">
        <div>
            <p class="font-black text-gray-900 text-lg">{{ $caja->sucursal->nombre }}</p>
            <p class="text-xs text-gray-400 mt-0.5">
                Abierta el {{ $caja->created_at->format('d/m/Y') }} a las {{ $caja->created_at->format('H:i') }}
                · por {{ $caja->user->name }}
            </p>
        </div>
        <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-green-100 text-green-700">Abierta</span>
    </div>

    {{-- Resumen de ventas del día --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Ventas del período</h3>
            <p class="text-xs text-gray-400 mt-0.5">Todas las formas de pago</p>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($pagosPorTipo as $tipo => $monto)
            <div class="flex justify-between items-center px-6 py-3.5 text-sm">
                <div class="flex items-center gap-2">
                    @if($tipo === 'efectivo')
                        <span class="w-2 h-2 rounded-full bg-green-400 shrink-0"></span>
                        <span class="text-gray-700 capitalize">Efectivo</span>
                        <span class="text-xs text-gray-400">(entra a caja)</span>
                    @elseif($tipo === 'tarjeta')
                        <span class="w-2 h-2 rounded-full bg-blue-400 shrink-0"></span>
                        <span class="text-gray-700 capitalize">Tarjeta</span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-purple-400 shrink-0"></span>
                        <span class="text-gray-700 capitalize">{{ $tipo }}</span>
                    @endif
                </div>
                <span class="font-semibold tabular-nums {{ $tipo === 'efectivo' ? 'text-green-600' : 'text-gray-500' }}">
                    ${{ number_format($monto, 2) }}
                </span>
            </div>
            @empty
            <div class="px-6 py-4 text-sm text-gray-400">Sin ventas registradas en este período.</div>
            @endforelse
            <div class="flex justify-between items-center px-6 py-3.5 bg-gray-50">
                <span class="text-sm font-bold text-gray-700">Total vendido</span>
                <span class="font-black text-gray-900 tabular-nums">${{ number_format($totalVentasDia, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Devoluciones del período --}}
    @if($movsDevoluciones->isNotEmpty())
    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Devoluciones</h3>
            <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">
                − ${{ number_format($movsDevoluciones->sum('monto'), 2) }}
            </span>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($movsDevoluciones as $mov)
            <div class="flex justify-between items-center px-6 py-3 text-sm">
                <span class="text-gray-600">{{ $mov->concepto }}</span>
                <span class="font-semibold tabular-nums text-amber-600">− ${{ number_format($mov->monto, 2) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Composición de caja --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Composición de caja</h3>
            <p class="text-xs text-gray-400 mt-0.5">Solo movimientos en efectivo</p>
        </div>
        <div class="divide-y divide-gray-50">
            <div class="flex justify-between items-center px-6 py-3.5 text-sm">
                <span class="text-gray-600">Fondo inicial</span>
                <span class="font-semibold tabular-nums text-gray-700">${{ number_format($caja->saldo_inicial, 2) }}</span>
            </div>
            <div class="flex justify-between items-center px-6 py-3.5 text-sm">
                <span class="text-gray-600">Efectivo de ventas</span>
                <span class="font-semibold tabular-nums text-green-600">+ ${{ number_format($efectivoVentas, 2) }}</span>
            </div>
            @if($entradasManuales > 0)
            <div class="flex justify-between items-center px-6 py-3.5 text-sm">
                <span class="text-gray-600">Entradas manuales</span>
                <span class="font-semibold tabular-nums text-teal-600">+ ${{ number_format($entradasManuales, 2) }}</span>
            </div>
            @endif
            @if($salidas > 0)
            <div class="flex justify-between items-center px-6 py-3.5 text-sm">
                <span class="text-gray-600">Salidas</span>
                <span class="font-semibold tabular-nums text-red-500">− ${{ number_format($salidas, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between items-center px-6 py-4 bg-gray-50">
                <span class="font-bold text-gray-800">Saldo esperado en caja</span>
                <span class="font-black text-gray-900 text-lg tabular-nums">${{ number_format($saldoEsperado, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Conteo y cierre --}}
    <form method="POST" action="{{ route('empleados.caja.cerrar', $caja) }}"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <h3 class="font-semibold text-gray-800">Conteo físico</h3>

        <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-500">Dinero contado en caja</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-lg">$</span>
                <input type="number" name="saldo_final" step="0.01" min="0" required
                       x-model.number="contado"
                       class="w-full border-2 border-gray-200 focus:border-brand-700 rounded-xl pl-10 pr-4 py-3.5 text-2xl font-black text-gray-900 focus:outline-none transition-colors tabular-nums">
            </div>
        </div>

        {{-- Diferencia en tiempo real --}}
        <div class="rounded-xl px-4 py-3.5 flex items-center justify-between"
             :class="contado - {{ $saldoEsperado }} === 0
                 ? 'bg-green-50 border border-green-200'
                 : (contado - {{ $saldoEsperado }} > 0
                     ? 'bg-blue-50 border border-blue-200'
                     : 'bg-red-50 border border-red-200')">
            <span class="text-sm font-semibold"
                  :class="contado - {{ $saldoEsperado }} === 0
                      ? 'text-green-700'
                      : (contado - {{ $saldoEsperado }} > 0 ? 'text-blue-700' : 'text-red-700')">
                <span x-text="contado - {{ $saldoEsperado }} === 0
                    ? '✓ Cuadra exacto'
                    : (contado - {{ $saldoEsperado }} > 0 ? 'Sobrante' : 'Faltante')"></span>
            </span>
            <span class="font-black text-lg tabular-nums"
                  :class="contado - {{ $saldoEsperado }} === 0
                      ? 'text-green-700'
                      : (contado - {{ $saldoEsperado }} > 0 ? 'text-blue-700' : 'text-red-700')"
                  x-text="'$' + Math.abs(contado - {{ $saldoEsperado }}).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')">
            </span>
        </div>

        <div class="flex gap-3 pt-1">
            <a href="{{ route('empleados.caja.show', $caja) }}"
               class="flex-1 text-center py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="flex-1 py-3 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-sm font-bold transition-colors">
                Confirmar cierre
            </button>
        </div>
    </form>

</div>
@endsection
