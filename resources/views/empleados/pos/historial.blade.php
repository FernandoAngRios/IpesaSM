@extends('layouts.empleados')

@section('title', 'Historial de Ventas — IPESA SM')
@section('page-title', 'Historial de Ventas')

@section('header-actions')

{{-- Exportar ventas --}}
<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = !open" type="button"
            class="inline-flex items-center gap-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Exportar
        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-72 bg-white rounded-2xl border border-gray-100 shadow-xl z-50 p-4">

        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Exportar ventas (.xlsx)</p>

        <form action="{{ route('empleados.exportar.ventas') }}" method="GET" class="space-y-3">
            @if(request('sucursal_id'))
            <input type="hidden" name="sucursal_id" value="{{ request('sucursal_id') }}">
            @endif

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ request('desde') }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-700">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ request('hasta', now()->toDateString()) }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-700">
                </div>
            </div>

            <p class="text-[11px] text-gray-400">Deja vacío para exportar todas las ventas. Si tienes un almacén filtrado, se exporta solo ese.</p>

            <button type="submit"
                    class="w-full bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                Descargar Excel
            </button>
        </form>
    </div>
</div>

<a href="{{ route('empleados.pos.index') }}"
   class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    Ir al POS
</a>
@endsection

@section('content')
<div class="space-y-4">

    {{-- Filtro por almacén (visible para todos, útil para admin) --}}
    @if($sucursales->count() > 1)
    <form method="GET" action="{{ route('empleados.ventas.index') }}" class="flex items-center gap-3">
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
        <a href="{{ route('empleados.ventas.index') }}"
           class="text-sm text-gray-400 hover:text-gray-600 transition-colors">Limpiar</a>
        @endif
    </form>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        @if($ventas->isEmpty())
        <div class="py-20 text-center text-gray-400">
            <div class="text-5xl mb-3">🧾</div>
            <p class="font-medium">No hay ventas registradas aún</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Folio</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fecha</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Almacén</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Cliente</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pago</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Vendedor</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Total</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($ventas as $venta)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-bold text-sm text-gray-900">#{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-sm text-gray-700">{{ $venta->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $venta->created_at->format('H:i') }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-sm text-gray-700">{{ $venta->sucursal->nombre }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-sm text-gray-600">{{ $venta->cliente_nombre ?? '—' }}</span>
                            @if($venta->cliente_telefono)
                            <p class="text-xs text-gray-400">{{ $venta->cliente_telefono }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1">
                                @foreach($venta->pagos->groupBy('tipo') as $tipo => $pagos)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full capitalize
                                    {{ $tipo === 'efectivo' ? 'bg-green-50 text-green-700' : ($tipo === 'tarjeta' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700') }}">
                                    {{ $tipo }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="text-sm text-gray-700">{{ $venta->vendedor ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <span class="font-bold text-sm text-gray-900 tabular-nums">
                                ${{ number_format($venta->total, 2) }}
                            </span>
                            @if($venta->devoluciones->isNotEmpty())
                            <p class="text-xs text-amber-500 tabular-nums">
                                − ${{ number_format($venta->devoluciones->sum('total_devuelto'), 2) }} dev.
                            </p>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('empleados.ventas.show', $venta) }}"
                                   title="Ver detalle"
                                   class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-brand-700 hover:bg-brand-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('empleados.pos.ticket', $venta) }}"
                                   title="Ver ticket"
                                   class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-brand-700 hover:bg-brand-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($ventas->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $ventas->links() }}
        </div>
        @endif
        @endif

    </div>
</div>
@endsection
