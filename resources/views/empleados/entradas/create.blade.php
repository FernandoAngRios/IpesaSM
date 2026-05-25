@extends('layouts.empleados')

@section('title', 'Registrar Entrada — IPESA SM')
@section('page-title', 'Registrar entrada de material')

@section('content')
<div class="max-w-xl mx-auto"
     x-data="{
         productId: '{{ old('product_id') }}',
         productUnits: {{ json_encode($productUnits) }},
         unitAbbrs: {{ json_encode(\App\Support\Units::abbrs()) }},
         decimalUnits: {{ json_encode(\App\Support\Units::decimalUnits()) }},
         get unidadActual() {
             return this.productId ? (this.productUnits[this.productId] ?? 'litro') : 'litro';
         },
         get abbrActual() {
             return this.unitAbbrs[this.unidadActual] ?? this.unidadActual;
         },
         get isDecimalUnit() {
             return this.decimalUnits.includes(this.unidadActual);
         },
         get stepActual() {
             return this.isDecimalUnit ? 0.001 : 1;
         }
     }">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <a href="{{ route('empleados.entradas.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h2 class="font-bold text-gray-900">Material recibido de proveedor</h2>
    </div>

    <form action="{{ route('empleados.entradas.store') }}" method="POST">
        @csrf

        <div class="px-6 py-6 space-y-5">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Proveedor *</label>
                <input type="text" name="proveedor_nombre" value="{{ old('proveedor_nombre') }}" required
                       class="w-full border @error('proveedor_nombre') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                       placeholder="Ej: Pinturas del Valle S.A.">
                @error('proveedor_nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Almacén que recibe *</label>
                @if($sucursales->count() === 1)
                <input type="hidden" name="sucursal_id" value="{{ $sucursales->first()->id }}">
                <div class="w-full border border-gray-200 rounded-xl px-4 py-3 bg-gray-50 text-sm text-gray-700 font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    {{ $sucursales->first()->nombre }}
                </div>
                @else
                <select name="sucursal_id" required
                        class="w-full border @error('sucursal_id') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                    <option value="">Seleccionar almacén...</option>
                    @foreach($sucursales as $s)
                    <option value="{{ $s->id }}" {{ old('sucursal_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->nombre }}
                    </option>
                    @endforeach
                </select>
                @endif
                @error('sucursal_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Producto *</label>
                <select name="product_id" x-model="productId" required
                        class="w-full border @error('product_id') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                    <option value="">Seleccionar producto...</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} — {{ $p->category->name }}
                    </option>
                    @endforeach
                </select>
                @error('product_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Cantidad recibida (<span x-text="abbrActual"></span>) *
                </label>
                <div class="relative">
                    <input type="number" name="cantidad_litros" value="{{ old('cantidad_litros') }}"
                           :step="stepActual" :min="stepActual" required
                           class="w-full border @error('cantidad_litros') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 pr-14 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                           placeholder="0.000">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium" x-text="abbrActual"></span>
                </div>
                @error('cantidad_litros')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nota <span class="text-gray-400 font-normal">(opcional)</span></label>
                <textarea name="nota" rows="2" maxlength="500"
                          class="w-full border @error('nota') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors resize-none"
                          placeholder="Número de remisión, factura u observaciones...">{{ old('nota') }}</textarea>
            </div>

        </div>

        <div class="border-t border-gray-100 px-6 py-4 flex items-center justify-end gap-3 bg-gray-50/40">
            <a href="{{ route('empleados.entradas.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 transition-colors">Cancelar</a>
            <button type="submit"
                    class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors">
                Registrar entrada
            </button>
        </div>

    </form>
</div>
</div>
@endsection
