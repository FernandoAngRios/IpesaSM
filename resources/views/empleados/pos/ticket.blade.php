@extends('layouts.empleados')

@section('title', 'Ticket #' . str_pad($venta->id, 6, '0', STR_PAD_LEFT) . ' — IPESA SM')
@section('page-title', 'Ticket #' . str_pad($venta->id, 6, '0', STR_PAD_LEFT))

@section('content')
<div class="max-w-sm mx-auto space-y-4">

    {{-- Acciones (solo pantalla, ocultas al imprimir) --}}
    <div class="flex items-center gap-3 no-print">
        <a href="{{ route('empleados.pos.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver al POS
        </a>
        <span class="ml-auto text-xs text-gray-400 italic" id="print-status">Enviando a impresora…</span>
        <button onclick="window.print()"
                class="inline-flex items-center gap-2 border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Reimprimir
        </button>
    </div>

    {{-- Ticket (visible en pantalla y al imprimir) --}}
    <div class="ticket-paper bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4" id="ticket-print">

        {{-- Logo y datos del negocio --}}
        <div class="text-center space-y-1 border-b border-dashed border-gray-200 pb-4">
            <img src="{{ asset('images/logo/logo.png') }}" alt="IPESA SM" class="h-12 w-auto mx-auto mb-2">
            <p class="font-black text-gray-900 text-base">IPESA SM</p>
            <p class="text-xs text-gray-500">{{ $venta->sucursal->nombre }}</p>
            <p class="text-xs text-gray-400">{{ $venta->sucursal->direccion ?? '' }}</p>
        </div>

        {{-- Datos del ticket --}}
        <div class="space-y-1 text-xs border-b border-dashed border-gray-200 pb-4">
            <div class="flex justify-between">
                <span class="text-gray-500">Folio</span>
                <span class="font-bold text-gray-900">#{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Fecha</span>
                <span class="text-gray-700">{{ $venta->created_at->format('d/m/Y H:i') }}</span>
            </div>
            @if($venta->vendedor)
            <div class="flex justify-between">
                <span class="text-gray-500">Vendedor</span>
                <span class="text-gray-700">{{ $venta->vendedor }}</span>
            </div>
            @endif
            @if($venta->cliente_nombre || $venta->cliente_telefono)
            <div class="flex justify-between">
                <span class="text-gray-500">Cliente</span>
                <span class="text-gray-700">{{ $venta->cliente_nombre ?? $venta->cliente_telefono }}</span>
            </div>
            @endif
        </div>

        {{-- Ítems --}}
        <div class="space-y-2 border-b border-dashed border-gray-200 pb-4">
            @foreach($venta->items as $item)
            <div class="flex items-start gap-2">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-900 leading-snug">{{ $item->nombre_producto }}</p>
                    @if($item->nombre_presentacion)
                    <p class="text-xs text-gray-400">{{ $item->nombre_presentacion }}</p>
                    @endif
                    <p class="text-xs text-gray-500 tabular-nums">
                        {{ number_format($item->cantidad, \App\Support\Units::decimals($item->product?->unit ?? 'litro')) }} × ${{ number_format($item->precio_unitario, 2) }}
                    </p>
                    @if($item->codigo_color)
                    <div class="mt-1.5 inline-flex items-center gap-1.5 bg-violet-100 border border-violet-300 rounded px-2 py-0.5">
                        <span class="text-[10px] font-bold text-violet-500 uppercase tracking-wide">Color</span>
                        <span class="text-sm font-black text-violet-800 tracking-widest">{{ $item->codigo_color }}</span>
                    </div>
                    @endif
                </div>
                <span class="text-xs font-bold text-gray-900 tabular-nums shrink-0">
                    ${{ number_format($item->subtotal, 2) }}
                </span>
            </div>
            @endforeach
        </div>

        {{-- Totales y pagos --}}
        @php
            $subtotalItems = $venta->items->sum('subtotal');
            $totalPagado   = $venta->pagos->sum('monto');
            $descuento     = (float) $venta->descuento;
        @endphp
        <div class="space-y-1.5 border-b border-dashed border-gray-200 pb-4">

            @if($descuento > 0)
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Subtotal</span>
                <span class="text-xs font-semibold text-gray-900 tabular-nums">${{ number_format($subtotalItems, 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-red-500">Descuento</span>
                <span class="text-xs text-red-500 tabular-nums">− ${{ number_format($descuento, 2) }}</span>
            </div>
            @endif

            @foreach($venta->pagos as $pago)
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500 capitalize">
                    {{ $pago->tipo }}@if($pago->referencia) ({{ $pago->referencia }})@endif
                </span>
                <span class="text-xs text-gray-700 tabular-nums">${{ number_format($pago->monto, 2) }}</span>
            </div>
            @endforeach

            @if($totalPagado > $venta->total)
            <div class="flex justify-between items-center pt-1">
                <span class="text-xs font-bold text-gray-700">Cambio</span>
                <span class="text-xs font-bold text-gray-900 tabular-nums">${{ number_format($totalPagado - $venta->total, 2) }}</span>
            </div>
            @endif

            <div class="flex justify-between items-center pt-1 border-t border-gray-100">
                <span class="font-black text-sm text-gray-900">TOTAL</span>
                <span class="font-black text-sm text-gray-900 tabular-nums">${{ number_format($venta->total, 2) }}</span>
            </div>
        </div>

        {{-- Pie --}}
        <p class="text-center text-xs text-gray-400">¡Gracias por su compra!</p>

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

@push('scripts')
<script>
window.addEventListener('DOMContentLoaded', function () {
    window.print();
});
window.addEventListener('afterprint', function () {
    var status = document.getElementById('print-status');
    if (status) status.textContent = 'Impreso ✓';
    setTimeout(function () {
        window.location.href = '{{ route('empleados.pos.index') }}';
    }, 1500);
});
</script>
@endpush
