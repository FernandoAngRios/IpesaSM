@extends('layouts.empleados')

@section('title', $sucursal->nombre . ' — IPESA SM')
@section('page-title', $sucursal->nombre)

@section('header-actions')
@if(auth()->user()->isAdmin())
<a href="{{ route('empleados.almacenes.edit', $sucursal) }}"
   class="inline-flex items-center gap-2 border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
    Editar
</a>
@endif
<a href="{{ route('empleados.transferencias.create') }}"
   class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
    </svg>
    Nueva transferencia
</a>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    modalStock:   false,
    editProduct:  null,
    editStock:    0,
    editUnit:     'litro',
    productUnits: @js($products->pluck('unit', 'id')),
    unitAbbrs:    @js(\App\Support\Units::abbrs()),
    decimalUnits: @js(\App\Support\Units::decimalUnits()),
    get editAbbr() { return this.unitAbbrs[this.editUnit] ?? this.editUnit; },
    get editStep() { return this.decimalUnits.includes(this.editUnit) ? 0.001 : 1; },
}">

    <a href="{{ route('empleados.almacenes.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-brand-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Todos los almacenes
    </a>

    {{-- Info del almacén --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row">
            @if($sucursal->foto)
            <div class="sm:w-56 h-40 sm:h-auto shrink-0">
                <img src="{{ asset('images/sucursales/' . $sucursal->foto) }}"
                     alt="{{ $sucursal->nombre }}"
                     class="w-full h-full object-cover">
            </div>
            @endif
            <div class="px-6 py-5 flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <h2 class="font-bold text-xl text-gray-900">{{ $sucursal->nombre }}</h2>
                    @if($sucursal->activo)
                    <span class="bg-green-50 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">Activo</span>
                    @else
                    <span class="bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full">Inactivo</span>
                    @endif
                </div>
                <div class="flex items-start gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 shrink-0 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $sucursal->direccion }}
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    {{ $sucursal->telefono }}
                </div>
            </div>
        </div>
    </div>

    {{-- Inventario --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Inventario</h3>
            @if($puedeEditar)
            <button @click="modalStock = true; editProduct = null; editStock = 0; editUnit = 'litro'"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800 px-3 py-1.5 rounded-lg hover:bg-brand-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajustar stock
            </button>
            @else
            <span class="text-xs text-gray-400 font-medium">Solo lectura</span>
            @endif
        </div>

        @if($inventario->isEmpty())
        <div class="py-12 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-sm font-medium">Sin productos en inventario</p>
            <p class="text-xs mt-1">Ajusta el stock o recibe una transferencia para comenzar</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Producto</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Categoría</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Stock</th>
                        @if($puedeEditar)<th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide"></th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($inventario as $item)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-3">
                            <span class="font-semibold text-sm text-gray-900">{{ $item->product->name }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $item->product->category->color }}"></span>
                                {{ $item->product->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <span class="font-bold text-sm {{ $item->stock_litros > 0 ? 'text-gray-900' : 'text-red-500' }}">
                                {{ number_format($item->stock_litros, \App\Support\Units::decimals($item->product->unit ?? 'litro')) }}
                                {{ \App\Support\Units::abbr($item->product->unit ?? 'litro') }}
                            </span>
                        </td>
                        @if($puedeEditar)
                        <td class="px-6 py-3 text-right">
                            <button @click="modalStock = true; editProduct = {{ $item->product_id }}; editStock = {{ $item->stock_litros }}; editUnit = '{{ $item->product->unit ?? 'litro' }}'"
                                    class="text-xs font-semibold text-brand-700 hover:text-brand-800 px-2.5 py-1 rounded-lg hover:bg-brand-50 transition-colors">
                                Editar
                            </button>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Transferencias recientes --}}
    @if($transferencias->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Transferencias recientes</h3>
            <a href="{{ route('empleados.transferencias.index') }}"
               class="text-sm text-brand-700 hover:underline font-medium">Ver todas</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fecha</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Producto</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Origen</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Destino</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Cantidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($transferencias as $t)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-3 text-xs text-gray-500 whitespace-nowrap">
                            {{ $t->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $t->product->name }}</td>
                        <td class="px-6 py-3">
                            <span class="text-xs {{ $t->origen_id === $sucursal->id ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                {{ $t->origen->nombre }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs {{ $t->destino_id === $sucursal->id ? 'text-green-600 font-semibold' : 'text-gray-500' }}">
                                {{ $t->destino->nombre }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <span class="font-bold text-sm {{ $t->origen_id === $sucursal->id ? 'text-red-600' : 'text-green-600' }}">
                                {{ $t->origen_id === $sucursal->id ? '-' : '+' }}{{ number_format($t->cantidad_litros, \App\Support\Units::decimals($t->product->unit ?? 'litro')) }}
                                {{ \App\Support\Units::abbr($t->product->unit ?? 'litro') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Modal ajustar stock --}}
    <div x-show="modalStock"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @click.self="modalStock = false">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Ajustar stock</h3>
                <button @click="modalStock = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('empleados.almacenes.ajustar-stock', $sucursal) }}" method="POST">
                @csrf
                <div class="px-6 py-5 space-y-4">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Producto *</label>
                        <select name="product_id" required x-model="editProduct"
                                @change="editUnit = productUnits[$event.target.value] ?? 'litro'"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                            <option value="">Seleccionar producto</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" x-bind:selected="editProduct == {{ $product->id }}">
                                {{ $product->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stock (<span x-text="editAbbr"></span>) *</label>
                        <input type="number" name="stock_litros" x-model="editStock"
                               :step="editStep" min="0" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                               :placeholder="editStep === 1 ? '0' : '0.000'">
                        <p class="text-xs text-gray-400 mt-1">Este valor reemplazará el stock actual del producto en este almacén.</p>
                    </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3 bg-gray-50/40">
                    <button type="button" @click="modalStock = false"
                            class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 transition-colors">Cancelar</button>
                    <button type="submit"
                            class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors text-sm">
                        Guardar stock
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
