@extends('layouts.empleados')

@section('title', 'Movimiento #' . $transferencia->id . ' — IPESA SM')
@section('page-title', 'Detalle de movimiento')

@section('content')
<div class="max-w-xl mx-auto">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <a href="{{ route('empleados.transferencias.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <h2 class="font-bold text-gray-900">Movimiento #{{ $transferencia->id }}</h2>
            <p class="text-xs text-gray-400">{{ $transferencia->created_at->format('d/m/Y H:i') }}</p>
        </div>
        @if($transferencia->estado === 'pendiente')
        <span class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">En tránsito</span>
        @else
        <span class="text-xs font-semibold text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-full">Confirmada</span>
        @endif
    </div>

    <div class="px-6 py-6 space-y-5">

        {{-- Origen --}}
        <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3">
            <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Almacén de origen</p>
                <a href="{{ route('empleados.almacenes.show', $transferencia->origen_id) }}"
                   class="font-bold text-gray-900 hover:text-brand-700 transition-colors">
                    {{ $transferencia->origen->nombre }}
                </a>
            </div>
        </div>

        {{-- Producto y cantidad --}}
        <div class="flex items-center gap-4 bg-brand-50 rounded-xl px-4 py-4">
            <div class="w-10 h-10 bg-brand-700 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900">{{ $transferencia->product->name }}</p>
                <p class="text-xs text-gray-500">{{ $transferencia->product->category->name }}</p>
            </div>
            @php
                $tUnit = $transferencia->product->unit ?? 'litro';
                $tDec  = \App\Support\Units::decimals($tUnit);
                $tAbbr = \App\Support\Units::abbr($tUnit);
                $tStep = \App\Support\Units::isDecimal($tUnit) ? '0.001' : '1';
            @endphp
            <div class="text-right shrink-0">
                <p class="text-2xl font-black text-brand-700">{{ number_format($transferencia->cantidad_litros, $tDec) }}</p>
                <p class="text-xs text-brand-600 font-medium">{{ $tAbbr }}</p>
            </div>
        </div>

        {{-- Destino --}}
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-400">Almacén que recibió</p>
                <a href="{{ route('empleados.almacenes.show', $transferencia->destino_id) }}"
                   class="font-bold text-gray-900 hover:text-brand-700 transition-colors">
                    {{ $transferencia->destino->nombre }}
                </a>
            </div>
        </div>

        {{-- Meta --}}
        <div class="space-y-0 border border-gray-100 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50/50">
                <span class="text-xs text-gray-500">Registrado por</span>
                <span class="text-xs font-semibold text-gray-900">{{ $transferencia->user->name }}</span>
            </div>
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-gray-100">
                <span class="text-xs text-gray-500">Fecha</span>
                <span class="text-xs font-semibold text-gray-900">{{ $transferencia->created_at->format('d/m/Y \a \l\a\s H:i') }}</span>
            </div>
            @if($transferencia->nota)
            <div class="px-4 py-2.5 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-1">Nota de envío</p>
                <p class="text-sm text-gray-900">{{ $transferencia->nota }}</p>
            </div>
            @endif
            @if($transferencia->estado === 'confirmada')
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-gray-100">
                <span class="text-xs text-gray-500">Cantidad recibida</span>
                <span class="text-xs font-semibold text-gray-900">
                    {{ number_format($transferencia->cantidad_recibida ?? $transferencia->cantidad_litros, $tDec) }}
                    {{ $tAbbr }}
                    @if($transferencia->cantidad_recibida && $transferencia->cantidad_recibida < $transferencia->cantidad_litros)
                    <span class="text-amber-600 ml-1">(faltaron {{ number_format($transferencia->cantidad_litros - $transferencia->cantidad_recibida, $tDec) }})</span>
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-gray-100">
                <span class="text-xs text-gray-500">Confirmado por</span>
                <span class="text-xs font-semibold text-gray-900">{{ $transferencia->confirmadoPor?->name ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-gray-100">
                <span class="text-xs text-gray-500">Fecha de recepción</span>
                <span class="text-xs font-semibold text-gray-900">{{ $transferencia->confirmado_at?->format('d/m/Y \a \l\a\s H:i') ?? '—' }}</span>
            </div>
            @if($transferencia->nota_recepcion)
            <div class="px-4 py-2.5 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-1">Nota de recepción</p>
                <p class="text-sm text-gray-900">{{ $transferencia->nota_recepcion }}</p>
            </div>
            @endif
            @endif
        </div>

        {{-- Aviso si está pendiente pero el usuario no puede confirmar --}}
        @if($transferencia->estado === 'pendiente' && !auth()->user()->puedeOperarSucursal($transferencia->destino_id))
        <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-500">
            <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Solo el personal de <span class="font-semibold text-gray-700 mx-1">{{ $transferencia->destino->nombre }}</span> puede confirmar la recepción.
        </div>
        @endif

        {{-- Formulario de confirmación (solo destino o admin) --}}
        @if($transferencia->estado === 'pendiente' && auth()->user()->puedeOperarSucursal($transferencia->destino_id))
        <div class="border border-amber-200 bg-amber-50 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-amber-200">
                <p class="text-sm font-semibold text-amber-800">Confirmar recepción</p>
                <p class="text-xs text-amber-600 mt-0.5">Indica cuánto llegó realmente al almacén destino</p>
            </div>
            <form action="{{ route('empleados.transferencias.confirmar', $transferencia) }}" method="POST"
                  class="px-4 py-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                        Cantidad recibida (máx. {{ number_format($transferencia->cantidad_litros, $tDec) }} {{ $tAbbr }}) *
                    </label>
                    <input type="number" name="cantidad_recibida"
                           value="{{ old('cantidad_recibida', $transferencia->cantidad_litros) }}"
                           step="{{ $tStep }}" min="{{ $tStep }}" max="{{ $transferencia->cantidad_litros }}" required
                           class="w-full border @error('cantidad_recibida') border-red-400 @else border-amber-200 @enderror rounded-xl px-4 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400/30 focus:border-amber-400 transition-colors text-sm">
                    @error('cantidad_recibida')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nota <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <textarea name="nota_recepcion" rows="2" maxlength="500"
                              class="w-full border border-amber-200 rounded-xl px-4 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400/30 focus:border-amber-400 transition-colors resize-none text-sm"
                              placeholder="Observaciones de la recepción...">{{ old('nota_recepcion') }}</textarea>
                </div>
                <button type="submit"
                        class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold px-4 py-2.5 rounded-xl transition-colors text-sm">
                    Confirmar recepción
                </button>
            </form>
        </div>
        @endif

    </div>
</div>
</div>
@endsection
