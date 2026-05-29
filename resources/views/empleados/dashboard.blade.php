@extends('layouts.empleados')

@section('title', 'Dashboard — IPESA SM')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- ── KPIs ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Ventas hoy --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 space-y-1">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Ventas hoy</p>
            <p class="text-2xl font-black text-gray-900 tabular-nums">${{ number_format($ventasHoy, 0) }}</p>
            <p class="text-xs text-gray-400">
                {{ $numVentasHoy }} venta{{ $numVentasHoy !== 1 ? 's' : '' }}
                @if($devHoy > 0)
                <span class="text-red-400 ml-1">− ${{ number_format($devHoy, 0) }} dev.</span>
                @endif
            </p>
        </div>

        {{-- Ventas del mes --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 space-y-1">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ now()->translatedFormat('F') }}</p>
            <p class="text-2xl font-black text-gray-900 tabular-nums">${{ number_format($ventasMes, 0) }}</p>
            <p class="text-xs text-gray-400">Mes actual</p>
        </div>

        {{-- Saldo en cajas --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 space-y-1">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Saldo en cajas</p>
            <p class="text-2xl font-black text-gray-900 tabular-nums">${{ number_format($saldoCajas, 0) }}</p>
            <p class="text-xs text-gray-400">
                {{ $cajasAbiertas->count() }} caja{{ $cajasAbiertas->count() !== 1 ? 's' : '' }} abierta{{ $cajasAbiertas->count() !== 1 ? 's' : '' }}
            </p>
        </div>

        {{-- Mensajes sin leer --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 space-y-1">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sin atender</p>
            <p class="text-2xl font-black text-gray-900 tabular-nums">{{ $stats['unread_messages'] + $stats['unread_internal'] }}</p>
            <p class="text-xs text-gray-400">
                {{ $stats['unread_messages'] }} cliente{{ $stats['unread_messages'] !== 1 ? 's' : '' }}
                · {{ $stats['unread_internal'] }} interno{{ $stats['unread_internal'] !== 1 ? 's' : '' }}
            </p>
        </div>

    </div>

    {{-- ── Gráfica + Ventas recientes ─────────────────────────────────────── --}}
    <div class="grid lg:grid-cols-5 gap-4">

        {{-- Gráfica últimos 7 días --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 text-sm">Últimos 7 días</h2>
                <a href="{{ route('empleados.ventas.index') }}"
                   class="text-xs font-semibold text-brand-700 hover:text-brand-800">Ver historial →</a>
            </div>

            @php $totalSemana = $ventasSemana->sum('total'); @endphp

            {{-- Barras --}}
            <div class="flex items-end gap-2 h-32">
                @foreach($ventasSemana as $dia)
                <div class="flex-1 flex flex-col items-center gap-1 group">
                    {{-- Tooltip con monto --}}
                    <span class="text-[10px] font-semibold tabular-nums transition-opacity
                                 {{ $dia['total'] > 0 ? 'text-gray-500' : 'text-transparent' }}">
                        ${{ $dia['total'] >= 1000 ? number_format($dia['total'] / 1000, 1) . 'k' : number_format($dia['total'], 0) }}
                    </span>
                    {{-- Barra --}}
                    <div class="w-full rounded-t-lg transition-all"
                         style="height: {{ $dia['pct'] }}%"
                         :class="''"
                         class="{{ $dia['esHoy'] ? 'bg-brand-600' : 'bg-brand-200' }}"></div>
                </div>
                @endforeach
            </div>

            {{-- Etiquetas --}}
            <div class="flex gap-2 mt-1">
                @foreach($ventasSemana as $dia)
                <div class="flex-1 text-center">
                    <span class="text-[10px] {{ $dia['esHoy'] ? 'font-bold text-brand-700' : 'text-gray-400' }}">
                        {{ $dia['etiqueta'] }}
                    </span>
                </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                <span class="text-xs text-gray-400">Total semana</span>
                <span class="text-sm font-bold text-gray-900">${{ number_format($totalSemana, 0) }}</span>
            </div>
        </div>

        {{-- Ventas recientes --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-900 text-sm">Ventas recientes</h2>
                <a href="{{ route('empleados.ventas.index') }}"
                   class="text-xs font-semibold text-brand-700 hover:text-brand-800">Ver todas →</a>
            </div>

            @if($ventasRecientes->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <svg class="w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm text-gray-400">Sin ventas aún</p>
            </div>
            @else
            <div class="divide-y divide-gray-50">
                @foreach($ventasRecientes as $venta)
                <a href="{{ route('empleados.ventas.show', $venta) }}"
                   class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}
                        </p>
                        <p class="text-xs text-gray-400 truncate">
                            {{ $venta->sucursal->nombre }} · {{ $venta->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <span class="text-sm font-bold text-gray-900 tabular-nums shrink-0">
                        ${{ number_format($venta->total, 0) }}
                    </span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

    </div>

    {{-- ── Mensajes internos sin leer ──────────────────────────────────────── --}}
    @if($recentUnreadInternal->isNotEmpty())
    <div class="bg-white rounded-2xl border border-purple-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-purple-50 bg-purple-50/40">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h2 class="font-bold text-gray-900 text-sm">Mensajes internos sin leer</h2>
                <span class="bg-purple-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $stats['unread_internal'] }}</span>
            </div>
            <a href="{{ route('empleados.internal-messages.index') }}"
               class="text-xs font-semibold text-purple-600 hover:text-purple-700">Ver todos →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($recentUnreadInternal as $msg)
            <a href="{{ route('empleados.internal-messages.show', $msg) }}"
               class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ strtoupper(substr($msg->sender->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $msg->sender->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $msg->subject }}</p>
                </div>
                <span class="text-xs text-gray-400 shrink-0">{{ $msg->created_at->diffForHumans() }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Mensajes de clientes ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <h2 class="font-bold text-gray-900 text-sm">Mensajes de clientes</h2>
                @if($stats['unread_messages'] > 0)
                <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $stats['unread_messages'] }}</span>
                @endif
            </div>
            <a href="{{ route('empleados.messages.index') }}"
               class="text-xs font-semibold text-brand-700 hover:text-brand-800">Ver todos →</a>
        </div>

        @if($recentMessages->isEmpty())
        <div class="py-10 text-center text-gray-400 text-sm">Sin mensajes aún</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($recentMessages as $msg)
            <a href="{{ route('empleados.messages.show', $msg) }}"
               class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="w-8 h-8 rounded-full bg-brand-50 text-brand-700 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ strtoupper(substr($msg->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-sm text-gray-900 truncate">{{ $msg->name }}</span>
                        @if(! $msg->isRead())
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 truncate">{{ $msg->subject }}</p>
                </div>
                <span class="text-xs text-gray-400 shrink-0">{{ $msg->created_at->diffForHumans() }}</span>
            </a>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
