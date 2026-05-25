@extends('layouts.empleados')

@section('title', 'Devolución #' . str_pad($devolucion->id, 6, '0', STR_PAD_LEFT) . ' — IPESA SM')
@section('page-title', 'Devolución')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Volver --}}
    <a href="{{ route('empleados.devoluciones.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Devoluciones
    </a>

    {{-- Encabezado --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Devolución</p>
                <p class="text-2xl font-black text-gray-900">
                    #{{ str_pad($devolucion->id, 6, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <span class="shrink-0 bg-red-50 text-red-600 text-xs font-bold px-3 py-1.5 rounded-xl border border-red-100">
                − ${{ number_format($devolucion->total_devuelto, 2) }}
            </span>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div>
                <p class="text-xs text-gray-400">Venta</p>
                <a href="{{ route('empleados.ventas.show', $devolucion->venta) }}"
                   class="font-semibold text-brand-700 hover:text-brand-800 transition-colors">
                    #{{ str_pad($devolucion->venta_id, 6, '0', STR_PAD_LEFT) }}
                </a>
            </div>
            <div>
                <p class="text-xs text-gray-400">Almacén</p>
                <p class="font-semibold text-gray-900">{{ $devolucion->sucursal->nombre }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Registró</p>
                <p class="font-semibold text-gray-900">{{ $devolucion->user->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Fecha</p>
                <p class="font-semibold text-gray-900">{{ $devolucion->created_at->format('d/m/Y H:i') }}</p>
            </div>
            @if($devolucion->motivo)
            <div class="col-span-2">
                <p class="text-xs text-gray-400">Motivo</p>
                <p class="font-semibold text-gray-900">{{ $devolucion->motivo }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Productos devueltos</h3>
        </div>

        <div class="divide-y divide-gray-50">
            @foreach($devolucion->items as $item)
            <div class="px-6 py-4 flex items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 text-sm">{{ $item->nombre_producto }}</p>
                    @if($item->nombre_presentacion)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->nombre_presentacion }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ number_format($item->cantidad, 3) }} × ${{ number_format($item->precio_unitario, 2) }}
                    </p>
                </div>
                <p class="font-bold text-red-600 tabular-nums shrink-0">
                    − ${{ number_format($item->subtotal, 2) }}
                </p>
            </div>
            @endforeach
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-500">Total devuelto</p>
            <p class="text-xl font-black text-red-600 tabular-nums">
                − ${{ number_format($devolucion->total_devuelto, 2) }}
            </p>
        </div>
    </div>

</div>
@endsection
