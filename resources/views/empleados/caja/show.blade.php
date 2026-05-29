@extends('layouts.empleados')

@section('title', 'Caja #' . str_pad($caja->id, 4, '0', STR_PAD_LEFT) . ' — IPESA SM')
@section('page-title', 'Caja #' . str_pad($caja->id, 4, '0', STR_PAD_LEFT) . ' · ' . $caja->sucursal->nombre)

@section('header-actions')
<a href="{{ route('empleados.caja.index') }}"
   class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Volver
</a>
@if($caja->estado === 'cerrada')
<a href="{{ route('empleados.caja.imprimir', $caja) }}"
   class="inline-flex items-center gap-2 border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
    </svg>
    Imprimir cierre
</a>
@endif
@if($caja->estado === 'abierta')
<div class="flex items-center gap-2">
    {{-- Agregar movimiento --}}
    <div x-data="{ open: false, tipo: 'entrada' }" @click.outside="open = false" class="relative">
        <button @click="open = !open"
                class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Movimiento
        </button>
        <div x-show="open"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 top-full mt-2 w-72 bg-white rounded-2xl border border-gray-200 shadow-xl p-4 z-50">
            <form method="POST" action="{{ route('empleados.caja.movimiento', $caja) }}">
                @csrf
                {{-- Tipo --}}
                <div class="grid grid-cols-2 gap-1 bg-gray-100 p-1 rounded-xl mb-3">
                    <button type="button" @click="tipo = 'entrada'"
                            :class="tipo === 'entrada' ? 'bg-white shadow-sm text-green-700 font-semibold' : 'text-gray-500'"
                            class="py-1.5 rounded-lg text-xs transition-all">
                        ↑ Entrada
                    </button>
                    <button type="button" @click="tipo = 'salida'"
                            :class="tipo === 'salida' ? 'bg-white shadow-sm text-red-600 font-semibold' : 'text-gray-500'"
                            class="py-1.5 rounded-lg text-xs transition-all">
                        ↓ Salida
                    </button>
                    <input type="hidden" name="tipo" :value="tipo">
                </div>
                {{-- Concepto --}}
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Concepto</label>
                    <input type="text" name="concepto" required maxlength="200"
                           placeholder="Ej: Pago de proveedor"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                </div>
                {{-- Monto --}}
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Monto</label>
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                        <input type="number" name="monto" step="0.01" min="0.01" required
                               class="w-full border border-gray-200 rounded-lg pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                    </div>
                </div>
                <button type="submit"
                        class="w-full bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold py-2 rounded-lg transition-colors">
                    Registrar
                </button>
            </form>
        </div>
    </div>

    {{-- Cerrar caja --}}
    <a href="{{ route('empleados.caja.cierre', $caja) }}"
       class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Cerrar caja
    </a>
</div>
@endif
@endsection

@section('content')
@php
    $ventas          = $caja->totalVentas();
    $entradasManuales = $caja->totalEntradasManuales();
    $salidas          = $caja->totalSalidas();
    $saldo            = $caja->estado === 'abierta' ? $caja->saldoActual() : (float) $caja->saldo_final;
@endphp
<div class="space-y-6">

    @if(session('success'))
    <div class="bg-green-50 border border-green-100 rounded-xl px-4 py-3 text-sm text-green-700">
        {{ session('success') }}
    </div>
    @endif

    {{-- Resumen --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Fondo inicial</p>
            <p class="text-2xl font-black text-gray-900 tabular-nums">${{ number_format($caja->saldo_inicial, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Tickets</p>
            <p class="text-2xl font-black text-green-600 tabular-nums">+${{ number_format($ventas, 2) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Ventas en efectivo</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Entradas</p>
            <p class="text-2xl font-black text-teal-600 tabular-nums">+${{ number_format($entradasManuales, 2) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Con motivo</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Salidas</p>
            <p class="text-2xl font-black text-red-500 tabular-nums">-${{ number_format($salidas, 2) }}</p>
        </div>
        <div class="rounded-2xl border shadow-sm p-5
                    {{ $caja->estado === 'abierta' ? 'bg-brand-50 border-brand-100' : 'bg-white border-gray-100' }}">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">
                {{ $caja->estado === 'abierta' ? 'Saldo actual' : 'Cierre contado' }}
            </p>
            <p class="text-2xl font-black text-gray-900 tabular-nums">${{ number_format($saldo, 2) }}</p>
            @if($caja->estado === 'cerrada')
            @php $diferencia = (float) $caja->saldo_final - $caja->saldoActual(); @endphp
            @if($diferencia != 0)
            <p class="text-xs mt-1 font-semibold {{ $diferencia >= 0 ? 'text-green-600' : 'text-red-500' }}">
                {{ $diferencia >= 0 ? '+' : '' }}${{ number_format($diferencia, 2) }} vs. esperado
            </p>
            @else
            <p class="text-xs mt-1 font-semibold text-green-600">✓ Cuadra exacto</p>
            @endif
            @endif
        </div>
    </div>

    {{-- Info apertura/cierre --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex flex-wrap gap-6 text-sm">
        <div>
            <span class="text-gray-400 text-xs">Abierta por</span>
            <p class="font-semibold text-gray-800">{{ $caja->user->name }}</p>
        </div>
        <div>
            <span class="text-gray-400 text-xs">Fecha apertura</span>
            <p class="font-semibold text-gray-800">{{ $caja->created_at->format('d/m/Y H:i') }}</p>
        </div>
        @if($caja->estado === 'cerrada')
        <div>
            <span class="text-gray-400 text-xs">Cerrada por</span>
            <p class="font-semibold text-gray-800">{{ $caja->cerradoPor?->name ?? '—' }}</p>
        </div>
        <div>
            <span class="text-gray-400 text-xs">Fecha cierre</span>
            <p class="font-semibold text-gray-800">{{ $caja->cerrada_at?->format('d/m/Y H:i') }}</p>
        </div>
        @endif
        <div class="ml-auto">
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                {{ $caja->estado === 'abierta' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ ucfirst($caja->estado) }}
            </span>
        </div>
    </div>

    {{-- Movimientos --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Movimientos</h2>
        </div>
        @if($caja->movimientos->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <p class="font-medium">Sin movimientos registrados</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Hora</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipo</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Concepto</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Registró</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($caja->movimientos->sortByDesc('created_at') as $mov)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="text-sm text-gray-700">{{ $mov->created_at->format('H:i') }}</p>
                            <p class="text-xs text-gray-400">{{ $mov->created_at->format('d/m/Y') }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($mov->devolucion_id)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">↓ Devolución</span>
                            @elseif($mov->tipo === 'salida')
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-600">↓ Salida</span>
                            @elseif($mov->venta_id)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-700">↑ Ticket</span>
                            @else
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700">↑ Entrada</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-sm text-gray-700">{{ $mov->concepto }}</p>
                            @if($mov->venta_id)
                            <a href="{{ route('empleados.pos.ticket', $mov->venta_id) }}"
                               class="text-xs text-brand-600 hover:underline">
                                Ver ticket #{{ str_pad($mov->venta_id, 6, '0', STR_PAD_LEFT) }}
                            </a>
                            @elseif($mov->devolucion_id)
                            <a href="{{ route('empleados.ventas.show', $mov->devolucion->venta_id) }}"
                               class="text-xs text-amber-600 hover:underline">
                                Ver venta #{{ str_pad($mov->devolucion->venta_id, 6, '0', STR_PAD_LEFT) }}
                            </a>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-sm text-gray-600">{{ $mov->user->name }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            @if($mov->tipo === 'salida')
                                <span class="font-bold text-sm tabular-nums text-red-500">-${{ number_format($mov->monto, 2) }}</span>
                            @elseif($mov->venta_id)
                                <span class="font-bold text-sm tabular-nums text-green-600">+${{ number_format($mov->monto, 2) }}</span>
                            @else
                                <span class="font-bold text-sm tabular-nums text-teal-600">+${{ number_format($mov->monto, 2) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
