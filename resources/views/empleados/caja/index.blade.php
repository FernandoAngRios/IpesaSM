@extends('layouts.empleados')

@section('title', 'Caja — IPESA SM')
@section('page-title', 'Caja')

@section('header-actions')
@if($sucursalesSinCaja->isNotEmpty())
<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = !open"
            class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Abrir caja
    </button>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl border border-gray-200 shadow-xl p-4 z-50">
        <form method="POST" action="{{ route('empleados.caja.abrir') }}">
            @csrf
            @if($sucursalesSinCaja->count() > 1)
            <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Almacén</label>
                <select name="sucursal_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                    @foreach($sucursalesSinCaja as $s)
                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="sucursal_id" value="{{ $sucursalesSinCaja->first()->id }}">
            <p class="text-xs text-gray-500 mb-2">Almacén: <span class="font-semibold text-gray-700">{{ $sucursalesSinCaja->first()->nombre }}</span></p>
            @endif
            <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Fondo inicial</label>
                <div class="relative">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                    <input type="number" name="saldo_inicial" step="0.01" min="0" value="0" required
                           class="w-full border border-gray-200 rounded-lg pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                </div>
            </div>
            <button type="submit"
                    class="w-full bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold py-2 rounded-lg transition-colors">
                Abrir caja
            </button>
        </form>
    </div>
</div>
@endif
@endsection

@section('content')
<div class="space-y-6">

    {{-- Filtro por almacén --}}
    @if($sucursales->count() > 1)
    <form method="GET" action="{{ route('empleados.caja.index') }}" class="flex items-center gap-3">
        <select name="sucursal_id"
                onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
            <option value="">
                @if(auth()->user()->isAdmin()) Todos los almacenes @else Mis almacenes @endif
            </option>
            @foreach($sucursales as $s)
            <option value="{{ $s->id }}" @selected(request('sucursal_id') == $s->id)>
                {{ $s->nombre }}
            </option>
            @endforeach
        </select>
        @if(request('sucursal_id'))
        <a href="{{ route('empleados.caja.index') }}"
           class="text-sm text-gray-400 hover:text-gray-600 transition-colors">Limpiar</a>
        @endif
    </form>
    @endif

    {{-- Errores --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-sm text-red-600">
        {{ $errors->first() }}
    </div>
    @endif

    @if(session('success'))
    <div class="bg-green-50 border border-green-100 rounded-xl px-4 py-3 text-sm text-green-700">
        {{ session('success') }}
    </div>
    @endif

    {{-- Cajas abiertas --}}
    @if($abiertas->isNotEmpty())
    <div>
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Cajas abiertas</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($abiertas as $caja)
            @php
                $ventas           = $caja->totalVentas();
                $entradasManuales = $caja->totalEntradasManuales();
                $salidas          = $caja->totalSalidas();
                $saldo            = $caja->saldoActual();
            @endphp
            <a href="{{ route('empleados.caja.show', $caja) }}"
               class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-brand-200 transition-all group">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="font-bold text-gray-900">{{ $caja->sucursal->nombre }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Abierta {{ $caja->created_at->diffForHumans() }} · {{ $caja->user->name }}
                        </p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Abierta</span>
                </div>
                <div class="grid grid-cols-4 gap-2 text-center">
                    <div>
                        <p class="text-xs text-gray-400">Fondo</p>
                        <p class="font-bold text-sm text-gray-700 tabular-nums">${{ number_format($caja->saldo_inicial, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Tickets</p>
                        <p class="font-bold text-sm text-green-600 tabular-nums">+${{ number_format($ventas, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Entradas</p>
                        <p class="font-bold text-sm text-teal-600 tabular-nums">+${{ number_format($entradasManuales, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Salidas</p>
                        <p class="font-bold text-sm text-red-500 tabular-nums">-${{ number_format($salidas, 2) }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-gray-500">Saldo actual</span>
                    <span class="font-black text-base text-gray-900 tabular-nums">${{ number_format($saldo, 2) }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center text-gray-400">
        <div class="text-5xl mb-3">💵</div>
        <p class="font-medium">No hay cajas abiertas</p>
        @if($sucursalesSinCaja->isNotEmpty())
        <p class="text-sm mt-1">Abre una caja para empezar a registrar movimientos.</p>
        @endif
    </div>
    @endif

    {{-- Cajas cerradas --}}
    @if($cerradas->isNotEmpty())
    <div>
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Historial</h2>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Almacén</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Apertura</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Cierre</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Fondo</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Cierre contado</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($cerradas as $caja)
                        @php
                            $esperado    = (float) $caja->saldo_inicial
                                         + $caja->movimientos->where('tipo', 'entrada')->sum('monto')
                                         - $caja->movimientos->where('tipo', 'salida')->sum('monto');
                            $diferencia  = round((float) $caja->saldo_final - $esperado, 2);
                        @endphp
                        <tr class="transition-colors
                            @if($diferencia > 0) bg-blue-50/60 hover:bg-blue-50
                            @elseif($diferencia < 0) bg-red-50/60 hover:bg-red-50
                            @else hover:bg-gray-50/50
                            @endif">
                            <td class="px-5 py-3.5">
                                <span class="text-sm font-bold text-gray-900">#{{ str_pad($caja->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm text-gray-700">{{ $caja->sucursal->nombre }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm text-gray-700">{{ $caja->created_at->format('d/m/Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $caja->user->name }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm text-gray-700">{{ $caja->cerrada_at?->format('d/m/Y H:i') }}</p>
                                <p class="text-xs text-gray-400">{{ $caja->cerradoPor?->name }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm tabular-nums text-gray-700">${{ number_format($caja->saldo_inicial, 2) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="font-bold text-sm tabular-nums text-gray-900">${{ number_format($caja->saldo_final, 2) }}</span>
                                @if($diferencia !== 0.0)
                                <p class="text-xs tabular-nums {{ $diferencia > 0 ? 'text-blue-500' : 'text-red-500' }}">
                                    {{ $diferencia > 0 ? '+' : '−' }}${{ number_format(abs($diferencia), 2) }}
                                </p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('empleados.caja.imprimir', $caja) }}"
                                       title="Imprimir cierre"
                                       class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-brand-700 hover:bg-brand-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('empleados.caja.show', $caja) }}"
                                       title="Ver detalle"
                                       class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-brand-700 hover:bg-brand-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($cerradas->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $cerradas->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection
