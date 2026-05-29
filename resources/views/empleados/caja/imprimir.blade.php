@extends('layouts.empleados')

@section('title', 'Cierre de caja #' . str_pad($caja->id, 4, '0', STR_PAD_LEFT) . ' — IPESA SM')
@section('page-title', 'Cierre de caja')

@section('content')
@php
    $efectivoVentas   = $caja->totalVentas();
    $entradasManuales = $caja->totalEntradasManuales();
    $salidas          = $caja->totalSalidas();
    $saldoEsperado    = $caja->saldoActual();
    $saldoFinal       = (float) $caja->saldo_final;
    $diferencia       = $saldoFinal - $saldoEsperado;
    $totalVentas      = $pagosPorTipo->sum();
@endphp

<div class="max-w-sm mx-auto space-y-4">

    {{-- Acciones (solo pantalla) --}}
    <div class="flex items-center gap-3 no-print">
        <a href="{{ route('empleados.caja.show', $caja) }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Ver caja
        </a>
        <button onclick="window.print()"
                class="ml-auto inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir cierre
        </button>
    </div>

    {{-- Ticket --}}
    <div class="ticket-paper bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">

        {{-- Logo y negocio --}}
        <div class="text-center space-y-1 border-b border-dashed border-gray-200 pb-4">
            <img src="{{ asset('images/logo/logo.png') }}" alt="IPESA SM" class="h-12 w-auto mx-auto mb-2">
            <p class="font-black text-gray-900 text-base">IPESA SM</p>
            <p class="text-xs text-gray-500">{{ $caja->sucursal->nombre }}</p>
            <p class="text-xs font-bold text-gray-700 uppercase tracking-widest mt-1">Corte de caja</p>
        </div>

        {{-- Datos del corte --}}
        <div class="space-y-1 text-xs border-b border-dashed border-gray-200 pb-4">
            <div class="flex justify-between">
                <span class="text-gray-500">Folio caja</span>
                <span class="font-bold text-gray-900">#{{ str_pad($caja->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Apertura</span>
                <span class="text-gray-700">{{ $caja->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Cierre</span>
                <span class="text-gray-700">{{ $caja->cerrada_at?->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Abrió</span>
                <span class="text-gray-700">{{ $caja->user->name }}</span>
            </div>
            @if($caja->cerradoPor)
            <div class="flex justify-between">
                <span class="text-gray-500">Cerró</span>
                <span class="text-gray-700">{{ $caja->cerradoPor->name }}</span>
            </div>
            @endif
        </div>

        {{-- Ventas por método de pago --}}
        <div class="space-y-1 text-xs border-b border-dashed border-gray-200 pb-4">
            <p class="font-bold text-gray-700 uppercase tracking-wide text-[10px] mb-2">Ventas del período</p>
            @forelse($pagosPorTipo as $tipo => $monto)
            <div class="flex justify-between">
                <span class="text-gray-500 capitalize">{{ $tipo }}</span>
                <span class="font-semibold text-gray-800 tabular-nums">${{ number_format($monto, 2) }}</span>
            </div>
            @empty
            <p class="text-gray-400">Sin ventas</p>
            @endforelse
            <div class="flex justify-between pt-1 border-t border-gray-100 mt-1">
                <span class="font-bold text-gray-700">Total vendido</span>
                <span class="font-bold text-gray-900 tabular-nums">${{ number_format($totalVentas, 2) }}</span>
            </div>
        </div>

        {{-- Devoluciones --}}
        @if($movsDevoluciones->isNotEmpty())
        <div class="space-y-1 text-xs border-b border-dashed border-gray-200 pb-4">
            <p class="font-bold text-gray-700 uppercase tracking-wide text-[10px] mb-2">Devoluciones</p>
            @foreach($movsDevoluciones as $mov)
            <div class="flex justify-between">
                <span class="text-gray-500">{{ $mov->concepto }}</span>
                <span class="tabular-nums text-amber-600">− ${{ number_format($mov->monto, 2) }}</span>
            </div>
            @endforeach
            <div class="flex justify-between pt-1 border-t border-gray-100 mt-1">
                <span class="font-bold text-gray-700">Total devuelto</span>
                <span class="font-bold text-amber-600 tabular-nums">− ${{ number_format($movsDevoluciones->sum('monto'), 2) }}</span>
            </div>
        </div>
        @endif

        {{-- Composición de caja --}}
        <div class="space-y-1 text-xs border-b border-dashed border-gray-200 pb-4">
            <p class="font-bold text-gray-700 uppercase tracking-wide text-[10px] mb-2">Efectivo en caja</p>
            <div class="flex justify-between">
                <span class="text-gray-500">Fondo inicial</span>
                <span class="tabular-nums text-gray-700">${{ number_format($caja->saldo_inicial, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Ventas efectivo</span>
                <span class="tabular-nums text-gray-700">+ ${{ number_format($efectivoVentas, 2) }}</span>
            </div>
            @if($entradasManuales > 0)
            <div class="flex justify-between">
                <span class="text-gray-500">Entradas manuales</span>
                <span class="tabular-nums text-gray-700">+ ${{ number_format($entradasManuales, 2) }}</span>
            </div>
            @endif
            @if($salidas > 0)
            <div class="flex justify-between">
                <span class="text-gray-500">Salidas</span>
                <span class="tabular-nums text-gray-700">− ${{ number_format($salidas, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between pt-1 border-t border-gray-100 mt-1">
                <span class="font-bold text-gray-700">Saldo esperado</span>
                <span class="font-bold text-gray-900 tabular-nums">${{ number_format($saldoEsperado, 2) }}</span>
            </div>
        </div>

        {{-- Conteo y diferencia --}}
        <div class="space-y-1.5 text-xs">
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Dinero contado</span>
                <span class="font-bold text-gray-900 tabular-nums">${{ number_format($saldoFinal, 2) }}</span>
            </div>
            <div class="flex justify-between items-center pt-1.5 border-t border-gray-200">
                <span class="font-black text-sm text-gray-900">
                    @if($diferencia == 0) ✓ Cuadra exacto
                    @elseif($diferencia > 0) Sobrante
                    @else Faltante
                    @endif
                </span>
                <span class="font-black text-sm tabular-nums {{ $diferencia == 0 ? 'text-green-600' : ($diferencia > 0 ? 'text-blue-600' : 'text-red-600') }}">
                    @if($diferencia != 0)
                        {{ $diferencia > 0 ? '+' : '−' }} ${{ number_format(abs($diferencia), 2) }}
                    @else
                        $0.00
                    @endif
                </span>
            </div>
        </div>

    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; }
    aside, header { display: none !important; }
    .lg\:pl-64 { padding-left: 0 !important; }
    main { padding: 0 !important; }
    .ticket-paper {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        max-width: 80mm;
        margin: 0 auto;
    }
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
@endsection
