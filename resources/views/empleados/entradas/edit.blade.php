@extends('layouts.empleados')

@section('title', 'Editar Entrada #' . $entrada->id . ' — IPESA SM')
@section('page-title', 'Editar entrada')

@section('content')
@php
    $unit   = $entrada->product->unit ?? 'litro';
    $abbr   = \App\Support\Units::abbr($unit);
    $step   = in_array($unit, \App\Support\Units::decimalUnits()) ? '0.001' : '1';
    $minVal = $step;
@endphp
<div class="max-w-xl mx-auto">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <a href="{{ route('empleados.entradas.show', $entrada) }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="font-bold text-gray-900">Editar entrada #{{ $entrada->id }}</h2>
            <p class="text-xs text-gray-400">{{ $entrada->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- Aviso de impacto en stock --}}
    <div class="mx-6 mt-5 flex items-start gap-2.5 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
        <svg class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <p class="text-xs text-amber-700 leading-relaxed">
            Si cambias la cantidad, el stock del almacén se ajustará automáticamente con la diferencia.
        </p>
    </div>

    <form action="{{ route('empleados.entradas.update', $entrada) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="px-6 py-5 space-y-5">

            {{-- Producto (solo lectura) --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Producto</label>
                <div class="w-full border border-gray-200 rounded-xl px-4 py-3 bg-gray-50 text-sm text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="font-medium">{{ $entrada->product->name }}</span>
                    <span class="text-gray-400">—</span>
                    <span class="text-gray-500">{{ $entrada->product->category->name }}</span>
                </div>
            </div>

            {{-- Almacén (solo lectura) --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Almacén</label>
                <div class="w-full border border-gray-200 rounded-xl px-4 py-3 bg-gray-50 text-sm text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="font-medium">{{ $entrada->sucursal->nombre }}</span>
                </div>
            </div>

            {{-- Proveedor --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Proveedor *</label>
                <input type="text" name="proveedor_nombre"
                       value="{{ old('proveedor_nombre', $entrada->proveedor_nombre) }}" required
                       class="w-full border @error('proveedor_nombre') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                       placeholder="Ej: Pinturas del Valle S.A.">
                @error('proveedor_nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Cantidad --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Cantidad ({{ $abbr }}) *
                </label>
                <div class="relative">
                    <input type="number" name="cantidad_litros"
                           value="{{ old('cantidad_litros', $entrada->cantidad_litros) }}"
                           step="{{ $step }}" min="{{ $minVal }}" required
                           class="w-full border @error('cantidad_litros') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 pr-14 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">{{ $abbr }}</span>
                </div>
                @error('cantidad_litros')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Nota --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Nota <span class="text-gray-400 font-normal">(opcional)</span>
                </label>
                <textarea name="nota" rows="2" maxlength="500"
                          class="w-full border @error('nota') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors resize-none"
                          placeholder="Número de remisión, factura u observaciones...">{{ old('nota', $entrada->nota) }}</textarea>
            </div>

        </div>

        <div class="border-t border-gray-100 px-6 py-4 flex items-center justify-end gap-3 bg-gray-50/40">
            <a href="{{ route('empleados.entradas.show', $entrada) }}"
               class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 transition-colors">Cancelar</a>
            <button type="submit"
                    class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors">
                Guardar cambios
            </button>
        </div>

    </form>
</div>
</div>
@endsection
