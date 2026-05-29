@extends('layouts.empleados')

@section('title', 'Nueva Transferencia — IPESA SM')
@section('page-title', 'Nueva transferencia')

@section('content')
<div class="max-w-xl mx-auto"
     x-data="{
         origenId: '{{ old('origen_id', $origenSucursales->count() === 1 ? $origenSucursales->first()->id : '') }}',
         destinoId: '{{ old('destino_id') }}',
         productId: '{{ old('product_id') }}',
         stocks: {{ json_encode($stocks) }},
         productUnits: {{ json_encode($productUnits) }},
         unitAbbrs: {{ json_encode(\App\Support\Units::abbrs()) }},
         decimalUnits: {{ json_encode(\App\Support\Units::decimalUnits()) }},
         get stockDisponible() {
             if (!this.origenId || !this.productId) return null;
             const s = this.stocks[this.origenId];
             if (!s) return 0;
             return parseFloat(s[this.productId] ?? 0);
         },
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
         },
         get stockDisplay() {
             if (this.stockDisponible === null) return '—';
             return this.isDecimalUnit
                 ? this.stockDisponible.toFixed(3)
                 : Math.round(this.stockDisponible).toString();
         }
     }">

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <a href="{{ route('empleados.transferencias.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h2 class="font-bold text-gray-900">Transferir stock entre almacenes</h2>
    </div>

    <form action="{{ route('empleados.transferencias.store') }}" method="POST">
        @csrf

        <div class="px-6 py-6 space-y-5">

            <div class="grid sm:grid-cols-2 gap-4">
                {{-- Origen: solo sucursales del usuario --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Almacén de origen *</label>
                    @if($origenSucursales->count() === 1)
                    {{-- Una sola sucursal: preseleccionada, no modificable --}}
                    <input type="hidden" name="origen_id" value="{{ $origenSucursales->first()->id }}">
                    <div class="w-full border border-gray-200 rounded-xl px-4 py-3 bg-gray-50 text-sm text-gray-700 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ $origenSucursales->first()->nombre }}
                    </div>
                    @else
                    <select name="origen_id" x-model="origenId" required
                            class="w-full border @error('origen_id') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                        <option value="">Seleccionar...</option>
                        @foreach($origenSucursales as $s)
                        <option value="{{ $s->id }}" {{ old('origen_id') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                    @endif
                    @error('origen_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Destino: todos los almacenes; el JS excluye el seleccionado como origen --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Almacén de destino *</label>
                    <select name="destino_id" x-model="destinoId" required
                            class="w-full border @error('destino_id') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                        <option value="">Seleccionar...</option>
                        @foreach($destinoSucursales as $s)
                        <option value="{{ $s->id }}"
                                :disabled="origenId == {{ $s->id }}"
                                {{ old('destino_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('destino_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Producto *</label>
                <select name="product_id" x-model="productId" required
                        class="w-full border @error('product_id') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                    <option value="">Seleccionar producto...</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }} — {{ $p->category->name }}</option>
                    @endforeach
                </select>
                @error('product_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

                <div x-show="origenId && productId" class="mt-2">
                    <span x-show="stockDisponible > 0"
                          class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-lg">
                        Disponible en origen: <span class="font-bold" x-text="stockDisplay + ' ' + abbrActual"></span>
                    </span>
                    <span x-show="stockDisponible !== null && stockDisponible <= 0"
                          class="inline-flex items-center gap-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-3 py-1.5 rounded-lg">
                        Sin stock en el almacén de origen
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Cantidad a transferir (<span x-text="abbrActual"></span>) *
                </label>
                <div class="relative">
                    <input type="number" name="cantidad_litros" value="{{ old('cantidad_litros') }}"
                           :step="stepActual" :min="stepActual" required
                           :max="stockDisponible ?? undefined"
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
                          placeholder="Motivo de la transferencia...">{{ old('nota') }}</textarea>
            </div>

        </div>

        <div class="border-t border-gray-100 px-6 py-4 flex items-center justify-end gap-3 bg-gray-50/40">
            <a href="{{ route('empleados.transferencias.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 transition-colors">Cancelar</a>
            <button type="submit"
                    class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors">
                Registrar transferencia
            </button>
        </div>

    </form>
</div>
</div>
@endsection
