@extends('layouts.empleados')

@section('title', 'Venta #' . str_pad($venta->id, 6, '0', STR_PAD_LEFT) . ' — IPESA SM')
@section('page-title', 'Venta #' . str_pad($venta->id, 6, '0', STR_PAD_LEFT))

@section('header-actions')
    @php $totalDevuelto = $venta->totalDevuelto(); @endphp
    @if($totalDevuelto < (float) $venta->total)
        <a href="{{ route('empleados.devoluciones.create', $venta) }}"
           class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
            Registrar devolución
        </a>
    @endif
    <a href="{{ route('empleados.pos.ticket', $venta) }}"
       class="inline-flex items-center gap-2 border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Ver ticket
    </a>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Volver --}}
    <a href="{{ route('empleados.ventas.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Historial de ventas
    </a>

    {{-- Cabecera --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs text-gray-400">Folio</p>
                <h2 class="text-2xl font-black text-gray-900">#{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $venta->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="text-right space-y-1">
                @if($totalDevuelto > 0)
                    <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                        Devolución parcial
                    </span>
                @endif
                <p class="text-2xl font-black text-gray-900">${{ number_format($venta->total, 2) }}</p>
                <p class="text-xs text-gray-400">{{ $venta->sucursal->nombre }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-5 border-t border-gray-100 text-sm">
            @if($venta->vendedor)
            <div>
                <p class="text-xs text-gray-400">Vendedor</p>
                <p class="font-medium text-gray-800">{{ $venta->vendedor }}</p>
            </div>
            @endif
            @if($venta->cliente_nombre || $venta->cliente_telefono)
            <div>
                <p class="text-xs text-gray-400">Cliente</p>
                <p class="font-medium text-gray-800">{{ $venta->cliente_nombre ?? $venta->cliente_telefono }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs text-gray-400">Registró</p>
                <p class="font-medium text-gray-800">{{ $venta->user->name }}</p>
            </div>
        </div>
    </div>

    {{-- Productos --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Productos</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($venta->items as $item)
            <div class="flex items-start gap-4 px-6 py-4">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 text-sm">{{ $item->nombre_producto }}</p>
                    @if($item->nombre_presentacion)
                        <p class="text-xs text-gray-400">{{ $item->nombre_presentacion }}</p>
                    @endif
                    @if($item->codigo_color)
                        <span class="inline-flex items-center mt-1 bg-violet-100 text-violet-700 font-bold text-sm rounded-md px-2.5 py-1 tracking-wide">
                            {{ $item->codigo_color }}
                        </span>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm text-gray-500 tabular-nums">{{ number_format($item->cantidad, \App\Support\Units::decimals($item->product?->unit ?? 'litro')) }} × ${{ number_format($item->precio_unitario, 2) }}</p>
                    <p class="font-bold text-gray-900 tabular-nums">${{ number_format($item->subtotal, 2) }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Totales --}}
        <div class="px-6 py-4 bg-gray-50 space-y-1.5 border-t border-gray-100">
            @php $descuento = (float) $venta->descuento; @endphp
            @if($descuento > 0)
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Subtotal</span>
                <span class="tabular-nums">${{ number_format($venta->subtotalItems(), 2) }}</span>
            </div>
            <div class="flex justify-between text-sm text-red-500">
                <span>Descuento</span>
                <span class="tabular-nums">− ${{ number_format($descuento, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between font-black text-gray-900 text-base pt-1 border-t border-gray-200">
                <span>Total</span>
                <span class="tabular-nums">${{ number_format($venta->total, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Pagos --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Pagos</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($venta->pagos as $pago)
            <div class="flex justify-between items-center px-6 py-3 text-sm">
                <span class="capitalize text-gray-600">
                    {{ $pago->tipo }}@if($pago->referencia) — {{ $pago->referencia }}@endif
                </span>
                <span class="font-semibold tabular-nums">${{ number_format($pago->monto, 2) }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Devoluciones --}}
    @if($venta->devoluciones->isNotEmpty())
    <div class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Devoluciones</h3>
            <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full">
                Total devuelto: ${{ number_format($totalDevuelto, 2) }}
            </span>
        </div>
        @foreach($venta->devoluciones as $devolucion)
        <div class="px-6 py-4 space-y-3 @if(!$loop->last) border-b border-amber-50 @endif">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-400">{{ $devolucion->created_at->format('d/m/Y H:i') }}</span>
                <span class="font-bold text-amber-700 tabular-nums">− ${{ number_format($devolucion->total_devuelto, 2) }}</span>
            </div>
            @if($devolucion->motivo)
                <p class="text-xs text-gray-500 italic">"{{ $devolucion->motivo }}"</p>
            @endif
            <div class="space-y-1">
                @foreach($devolucion->items as $di)
                <div class="flex justify-between text-xs text-gray-600">
                    <span>{{ $di->nombre_producto }}@if($di->nombre_presentacion) — {{ $di->nombre_presentacion }}@endif</span>
                    <span class="tabular-nums">{{ number_format($di->cantidad, \App\Support\Units::decimals($di->ventaItem?->product?->unit ?? 'litro')) }} × ${{ number_format($di->precio_unitario, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
