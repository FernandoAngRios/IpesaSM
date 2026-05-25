@extends('layouts.empleados')

@section('title', 'Devolución — Venta #' . str_pad($venta->id, 6, '0', STR_PAD_LEFT))
@section('page-title', 'Devolución')

@section('content')
<div class="max-w-2xl mx-auto space-y-6"
     x-data="{
         lineas: @js($items->map(fn($i) => ['id' => $i->id, 'nombre' => $i->nombre_producto, 'presentacion' => $i->nombre_presentacion, 'disponible' => (float) $i->cantidad_disponible, 'precio' => (float) $i->precio_unitario, 'cantidad' => 0, 'seleccionado' => false, 'unit' => $i->product?->unit ?? 'litro'])->values()),
         unitAbbrs:    @js(\App\Support\Units::abbrs()),
         decimalUnits: @js(\App\Support\Units::decimalUnits()),

         get total() {
             return this.lineas.reduce((s, l) => s + (l.seleccionado ? Math.min(l.cantidad, l.disponible) * l.precio : 0), 0);
         },
         get haySeleccion() {
             return this.lineas.some(l => l.seleccionado && l.cantidad > 0);
         },
         fmt(n) { return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); },
         abbr(unit)  { return this.unitAbbrs[unit] ?? unit; },
         stepOf(unit){ return this.decimalUnits.includes(unit) ? 0.001 : 1; },
         fmtQty(qty, unit) {
             return this.decimalUnits.includes(unit)
                 ? parseFloat(qty).toFixed(3)
                 : Math.round(qty).toString();
         },
     }">

    {{-- Volver --}}
    <a href="{{ route('empleados.ventas.show', $venta) }}"
       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Venta #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}
    </a>

    {{-- Encabezado --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-6 py-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="font-semibold text-amber-800 text-sm">Registrar devolución</p>
            <p class="text-xs text-amber-600 mt-0.5">Selecciona los productos y la cantidad a devolver. El stock se restaurará automáticamente.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('empleados.devoluciones.store', $venta) }}" class="space-y-5">
        @csrf

        {{-- Items --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 text-sm">Productos a devolver</h3>
            </div>

            <div class="divide-y divide-gray-50">
                <template x-for="(linea, index) in lineas" :key="linea.id">
                    <div class="px-6 py-4 space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" x-model="linea.seleccionado"
                                   @change="if (linea.seleccionado) linea.cantidad = linea.disponible"
                                   class="mt-0.5 w-4 h-4 rounded text-amber-500 border-gray-300 focus:ring-amber-400 cursor-pointer">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm" x-text="linea.nombre"></p>
                                <p class="text-xs text-gray-400" x-show="linea.presentacion" x-text="linea.presentacion"></p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Disponible: <span class="font-semibold text-gray-600" x-text="fmtQty(linea.disponible, linea.unit) + ' ' + abbr(linea.unit)"></span> ×
                                    $<span x-text="fmt(linea.precio)"></span>
                                </p>
                            </div>
                        </label>

                        <div x-show="linea.seleccionado" class="pl-7 flex items-center gap-3">
                            <label class="text-xs text-gray-500 shrink-0">Cantidad:</label>
                            <input type="number"
                                   x-model.number="linea.cantidad"
                                   :max="linea.disponible"
                                   :name="`items[${index}][cantidad]`"
                                   :min="stepOf(linea.unit)" :step="stepOf(linea.unit)"
                                   class="w-28 border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-gray-800 font-semibold focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400"
                                   @input="if (linea.cantidad > linea.disponible) linea.cantidad = linea.disponible">
                            <span class="text-xs text-gray-400">máx. <span x-text="fmtQty(linea.disponible, linea.unit) + ' ' + abbr(linea.unit)"></span></span>
                            {{-- Hidden id field --}}
                            <input type="hidden" :name="`items[${index}][id]`" :value="linea.id">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Motivo --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 space-y-2">
            <label class="text-xs font-semibold text-gray-500">Motivo (opcional)</label>
            <input type="text" name="motivo" maxlength="255"
                   placeholder="Ej. Producto defectuoso, error en pedido…"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400">
        </div>

        {{-- Total y confirmar --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs text-gray-400">Total a devolver</p>
                <p class="text-2xl font-black text-amber-600 tabular-nums">$<span x-text="fmt(total)"></span></p>
            </div>
            <button type="submit"
                    :disabled="!haySeleccion"
                    class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold text-sm px-6 py-3 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                Confirmar devolución
            </button>
        </div>

    </form>
</div>
@endsection
