@extends('layouts.empleados')
@section('title', 'Nueva Solicitud de Mercancía')
@section('page-title', 'Nueva Solicitud de Mercancía')

@section('content')
@php $sucursalUnica = $misSucursal->count() === 1 ? $misSucursal->first() : null; @endphp

<div x-data="solicitudForm({{ json_encode([
    'products' => $products->map(fn($p) => [
        'id'       => $p->id,
        'name'     => $p->name,
        'unit'     => $p->unit,
        'category' => $p->category->name,
    ])->values(),
    'stocks'        => $stocks,
    'unitAbbrs'     => \App\Support\Units::abbrs(),
    'decimalUnits'  => \App\Support\Units::decimalUnits(),
    'solicitanteId' => old('solicitante_sucursal_id', $sucursalUnica?->id ?? ''),
    'origenId'      => old('origen_sucursal_id', ''),
]) }})" class="max-w-2xl mx-auto space-y-4">

    <div class="flex items-center gap-3">
        <a href="{{ route('empleados.solicitudes.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h2 class="font-bold text-gray-900">Nueva solicitud</h2>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('empleados.solicitudes.store') }}" class="space-y-4">
        @csrf

        {{-- Sucursales --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">

            {{-- Sucursal solicitante --}}
            @if($sucursalUnica)
                <input type="hidden" name="solicitante_sucursal_id" value="{{ $sucursalUnica->id }}">
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-400">Solicita:</span>
                    <span class="font-semibold text-gray-800">{{ $sucursalUnica->nombre }}</span>
                </div>
            @else
                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1.5">Mi almacén (solicita)</label>
                    <select name="solicitante_sucursal_id" required x-model="solicitanteId"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                        <option value="">— Selecciona —</option>
                        @foreach($misSucursal as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Sucursal origen --}}
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-1.5">Pide mercancía a</label>
                <select name="origen_sucursal_id" required x-model="origenId"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                    <option value="">— Selecciona almacén —</option>
                    @foreach($todas->filter(fn($s) => !$sucursalUnica || $s->id !== $sucursalUnica->id) as $s)
                    <option value="{{ $s->id }}" {{ old('origen_sucursal_id') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Notas --}}
            <div>
                <textarea name="notas_solicitud" rows="2" maxlength="500"
                          placeholder="Notas o urgencia (opcional)..."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 resize-none text-gray-700 placeholder-gray-300">{{ old('notas_solicitud') }}</textarea>
            </div>
        </div>

        {{-- Lista de productos --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            {{-- Buscador fijo en el tope --}}
            <div class="px-4 py-3 border-b border-gray-100 relative">
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 focus-within:border-brand-400 focus-within:ring-2 focus-within:ring-brand-700/10 transition-all">
                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                    <input type="text" x-model="busqueda" @input="filtrar()" @keydown.enter.prevent
                           placeholder="Busca un producto y agrégalo a la lista..."
                           autocomplete="off"
                           class="flex-1 bg-transparent text-sm text-gray-700 placeholder-gray-300 focus:outline-none">
                    <button x-show="busqueda" type="button" @click="busqueda=''; filtrados=[]"
                            class="text-gray-300 hover:text-gray-500 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Dropdown --}}
                <div x-show="filtrados.length > 0" x-transition
                     class="absolute left-4 right-4 top-full -mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-30 overflow-hidden">
                    <template x-for="p in filtrados.slice(0, 7)" :key="p.id">
                        <button type="button" @click="agregar(p)"
                                class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-brand-50 text-left transition-colors border-b border-gray-50 last:border-0">
                            <div class="min-w-0">
                                <span class="text-sm font-medium text-gray-800" x-text="p.name"></span>
                                <span class="text-xs text-gray-400 ml-2" x-text="p.category"></span>
                            </div>
                            <span x-show="origenId"
                                  class="text-xs tabular-nums shrink-0 ml-3"
                                  :class="stockOrigen(p.id) > 0 ? 'text-gray-400' : 'text-red-300'"
                                  x-text="formatStock(stockOrigen(p.id), p.unit)"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Estado vacío --}}
            <div x-show="items.length === 0" class="py-12 text-center">
                <p class="text-sm text-gray-300">Busca productos arriba para agregarlos</p>
            </div>

            {{-- Items --}}
            <div x-show="items.length > 0">
                <template x-for="(item, i) in items" :key="item.product_id">
                    <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors group">
                        <input type="hidden" :name="`items[${i}][product_id]`" :value="item.product_id">

                        {{-- Nombre y categoría --}}
                        <div class="flex-1 min-w-0">
                            <span class="text-sm font-medium text-gray-800 truncate" x-text="item.name"></span>
                            <span class="text-xs text-gray-300 ml-1.5" x-text="item.category"></span>
                        </div>

                        {{-- Stock disponible en origen (sutil) --}}
                        <span x-show="origenId"
                              class="text-xs tabular-nums shrink-0 w-16 text-right"
                              :class="stockOrigen(item.product_id) > 0 ? 'text-gray-300' : 'text-red-300'"
                              x-text="formatStock(stockOrigen(item.product_id), item.unit)"></span>

                        {{-- Stepper cantidad --}}
                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden shrink-0">
                            <button type="button"
                                    @click="item.cantidad = +(Math.max(stepOf(item.unit), parseFloat(item.cantidad) - stepOf(item.unit))).toFixed(3)"
                                    class="px-2 py-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors text-sm font-bold select-none">−</button>
                            <input type="number" :name="`items[${i}][cantidad]`"
                                   x-model="item.cantidad"
                                   :step="stepOf(item.unit)"
                                   :min="stepOf(item.unit)"
                                   required
                                   class="w-14 text-center py-1.5 text-sm font-semibold text-gray-700 focus:outline-none border-x border-gray-200">
                            <button type="button"
                                    @click="item.cantidad = +(parseFloat(item.cantidad) + stepOf(item.unit)).toFixed(3)"
                                    class="px-2 py-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors text-sm font-bold select-none">+</button>
                        </div>

                        {{-- Quitar --}}
                        <button type="button" @click="quitar(i)"
                                class="w-6 h-6 flex items-center justify-center rounded-md text-gray-200 hover:text-red-400 hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                {{-- Total de productos --}}
                <div class="px-4 py-2 bg-gray-50/50 border-t border-gray-100">
                    <p class="text-xs text-gray-400" x-text="`${items.length} producto(s) en la solicitud`"></p>
                </div>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('empleados.solicitudes.index') }}"
               class="px-4 py-2.5 text-sm text-gray-400 hover:text-gray-600 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    :disabled="items.length === 0 || !solicitanteId || !origenId"
                    :class="items.length > 0 && solicitanteId && origenId
                        ? 'bg-brand-700 hover:bg-brand-800 text-white cursor-pointer shadow-sm'
                        : 'bg-gray-100 text-gray-300 cursor-not-allowed'"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all">
                Enviar solicitud
            </button>
        </div>
    </form>
</div>

<script>
function solicitudForm(cfg) {
    return {
        solicitanteId: cfg.solicitanteId,
        origenId:      cfg.origenId,
        busqueda:      '',
        filtrados:     [],
        items:         [],
        products:      cfg.products,
        stocks:        cfg.stocks,
        unitAbbrs:     cfg.unitAbbrs,
        decimalUnits:  cfg.decimalUnits,

        /* ── helpers de unidad ── */
        abbr(unit)  { return this.unitAbbrs[unit] ?? unit; },
        isDecimal(unit) { return this.decimalUnits.includes(unit); },
        stepOf(unit)    { return this.isDecimal(unit) ? 0.001 : 1; },
        formatStock(qty, unit) {
            const val = parseFloat(qty);
            return (this.isDecimal(unit) ? val.toFixed(1) : Math.round(val).toString())
                + ' ' + this.abbr(unit);
        },

        /* ── búsqueda ── */
        filtrar() {
            const q = this.busqueda.toLowerCase().trim();
            if (!q) { this.filtrados = []; return; }
            const yaAgregados = this.items.map(i => i.product_id);
            this.filtrados = this.products.filter(p =>
                !yaAgregados.includes(p.id) &&
                p.name.toLowerCase().includes(q)
            );
        },

        agregar(product) {
            this.items.push({
                product_id: product.id,
                name:       product.name,
                unit:       product.unit,
                category:   product.category,
                cantidad:   1,
            });
            this.busqueda  = '';
            this.filtrados = [];
        },

        quitar(index) {
            this.items.splice(index, 1);
        },

        stockOrigen(productId) {
            if (!this.origenId) return 0;
            return parseFloat(this.stocks?.[this.origenId]?.[productId] ?? 0);
        },
    };
}
</script>
@endsection
