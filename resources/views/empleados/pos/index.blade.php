@extends('layouts.empleados')

@section('title', 'Punto de Venta — IPESA SM')
@section('page-title', 'Punto de Venta')

@section('content')
<div x-data="pos({{ json_encode([
    'ventaId'    => $active?->id,
    'sucursalId' => $active?->sucursal_id,
    'csrf'       => csrf_token(),
    'buscarUrl'   => route('empleados.pos.buscar'),
    'unitAbbrs'   => \App\Support\Units::abbrs(),
    'decimalUnits' => \App\Support\Units::decimalUnits(),
    'itemsInit'  => $active ? $active->items->map(fn($i) => [
        'id'                  => $i->id,
        'nombre_producto'     => $i->nombre_producto,
        'nombre_presentacion' => $i->nombre_presentacion,
        'codigo_color'        => $i->codigo_color,
        'precio_unitario'     => (float) $i->precio_unitario,
        'cantidad'            => (float) $i->cantidad,
        'subtotal'            => (float) $i->subtotal,
    ])->values()->all() : [],
    'totalInit'  => (float) ($active?->total ?? 0),
]) }})">

    {{-- ── Tabs ────────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 mb-3">

        <div class="flex items-center gap-1.5 overflow-x-auto flex-1 min-w-0 pb-0.5">
            @foreach($tickets as $i => $t)
            <a href="{{ route('empleados.pos.index', ['ticket' => $t->id]) }}"
               class="{{ $active?->id === $t->id ? 'btn-pos-tab-active' : 'btn-pos-tab-inactive' }}">
                Ticket {{ $i + 1 }}
            </a>
            @endforeach
        </div>

        @if($sucursales->isNotEmpty())
        @if($sucursales->count() === 1)
        <form method="POST" action="{{ route('empleados.pos.store') }}" class="shrink-0">
            @csrf
            <input type="hidden" name="sucursal_id" value="{{ $sucursales->first()->id }}">
            <button type="submit" id="btn-nuevo-ticket"
                    class="btn-pos-nuevo">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo
                <span class="ml-1 text-[10px] font-normal opacity-50">F6</span>
            </button>
        </form>
        @else
        <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" id="btn-nuevo-ticket"
                    class="btn-pos-nuevo">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo
                <span class="ml-1 text-[10px] font-normal opacity-50">F6</span>
            </button>
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 top-full mt-2 w-52 bg-white rounded-2xl border border-gray-200 shadow-xl p-4 z-50">
                <form method="POST" action="{{ route('empleados.pos.store') }}">
                    @csrf
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Almacén</label>
                    <select name="sucursal_id" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                        @foreach($sucursales as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="w-full bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold py-2 rounded-lg transition-colors">
                        Abrir ticket
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endif
    </div>

    {{-- ── Sin ticket activo ───────────────────────────────────────────── --}}
    @if(!$active)
    <div class="flex items-center justify-center h-[calc(100vh-14rem)]">
        <div class="text-center space-y-5 max-w-xs">
            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Sin ticket activo</p>
                <p class="text-sm text-gray-400 mt-1">Abre un ticket para empezar</p>
            </div>
            @if($sucursales->isNotEmpty())
            <form method="POST" action="{{ route('empleados.pos.store') }}" class="space-y-2.5">
                @csrf
                @if($sucursales->count() > 1)
                <select name="sucursal_id" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                    @foreach($sucursales as $s)
                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
                @else
                <input type="hidden" name="sucursal_id" value="{{ $sucursales->first()->id }}">
                @endif
                <button type="submit"
                        class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold py-2.5 rounded-xl transition-colors text-sm">
                    Abrir ticket
                </button>
            </form>
            @else
            <p class="text-sm text-red-400">Sin almacenes asignados.</p>
            @endif
        </div>
    </div>

    @else
    {{-- ── POS: tarjeta del ticket (full width) ────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col h-[calc(100vh-12rem)]">

        {{-- Header --}}
        <div class="px-4 py-2.5 border-b border-gray-100 flex items-center justify-between shrink-0 gap-3">
            <div class="flex items-center gap-2 min-w-0 flex-wrap">
                <span class="font-bold text-gray-900 text-sm truncate">Ticket #{{ $active->id }}</span>
                <span class="text-gray-300">·</span>
                <span class="text-sm text-gray-400 truncate">{{ $active->sucursal->nombre }}</span>
                <span class="text-gray-200 hidden sm:inline">·</span>
                @if($vendedores->isNotEmpty())
                <div class="hidden sm:flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <select x-model="vendedor"
                            class="text-sm text-gray-700 bg-transparent border-0 border-b border-dashed border-gray-200 focus:border-brand-400 focus:outline-none py-0.5 pr-5 transition-colors cursor-pointer">
                        <option value="">¿Quién atiende?</option>
                        @foreach($vendedores as $v)
                        <option value="{{ $v->nombre }}">{{ $v->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                {{-- Producto libre --}}
                <button @click="abrirLibre()" type="button" title="Agregar producto libre"
                        class="btn-pos-libre">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Libre
                    <span class="ml-1 text-[10px] font-normal opacity-50">Ctrl+P</span>
                </button>
                {{-- Buscar --}}
                <button @click="abrirBuscador()" type="button"
                        class="btn-pos-buscar">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                    Buscar
                    <span class="ml-1 text-white/60 text-[10px] font-normal">F10</span>
                </button>
                {{-- Entrada de dinero --}}
                <button type="button" @click="movimientoCaja = { open: true, tipo: 'entrada' }"
                        class="btn-pos-entrada">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Entrada
                    <span class="ml-1 text-[10px] font-normal opacity-60">F7</span>
                </button>
                {{-- Salida de dinero --}}
                <button type="button" @click="movimientoCaja = { open: true, tipo: 'salida' }"
                        class="btn-pos-salida">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                    </svg>
                    Salida
                    <span class="ml-1 text-[10px] font-normal opacity-60">F8</span>
                </button>
                {{-- Cancelar ticket --}}
                <form action="{{ route('empleados.pos.cancelar', $active) }}" method="POST"
                      onsubmit="return confirm('¿Cancelar este ticket?')">
                    @csrf
                    <button type="submit" title="Cancelar ticket"
                            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-400 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Items (scrollable) --}}
        <div class="flex-1 overflow-y-auto min-h-0 relative">

            {{-- Marca de agua --}}
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                <img src="{{ asset('images/logo/logo.png') }}" alt=""
                     class="w-64 opacity-40 select-none">
            </div>

            {{-- Estado vacío --}}
            <template x-if="items.length === 0">
                <div class="flex flex-col items-center justify-center h-full gap-3 text-center">
                    <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm text-gray-300 font-medium">Ticket vacío — escanea o busca un producto</p>
                </div>
            </template>

            <template x-for="item in items" :key="item.id">
                <div class="item-row group">

                    {{-- Fila 1: nombre + subtotal + eliminar --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0" x-data="{ editCodigo: false, tmpCodigo: '' }">
                            <p class="item-name" x-text="item.nombre_producto"></p>
                            <span x-show="item.nombre_presentacion"
                                  class="item-badge"
                                  x-text="item.nombre_presentacion"></span>

                            {{-- Código color --}}
                            <span x-show="!editCodigo && item.codigo_color"
                                  class="inline-flex items-center mt-1 bg-violet-100 text-violet-700 font-bold text-sm rounded-md px-2.5 py-1 cursor-pointer hover:bg-violet-200 transition-colors tracking-wide"
                                  @click="editCodigo = true; tmpCodigo = item.codigo_color"
                                  title="Clic para editar"
                                  x-text="item.codigo_color"></span>

                            <button x-show="!editCodigo && !item.codigo_color"
                                    @click="editCodigo = true; tmpCodigo = ''"
                                    type="button"
                                    class="mt-0.5 text-[10px] text-violet-400 hover:text-violet-600 transition-colors">
                                + código de color
                            </button>

                            <span x-show="editCodigo" class="inline-flex items-center gap-1 mt-1">
                                <input type="text" x-model="tmpCodigo"
                                       class="text-xs text-violet-700 font-semibold bg-violet-50 border border-violet-200 rounded-md px-2 py-0.5 w-28 focus:outline-none focus:ring-1 focus:ring-violet-400"
                                       placeholder="Código…"
                                       @keydown.enter.prevent="saveCodigo(item, tmpCodigo); editCodigo = false"
                                       @keydown.escape="editCodigo = false"
                                       @blur="saveCodigo(item, tmpCodigo); editCodigo = false"
                                       x-effect="if (editCodigo) $nextTick(() => $el.focus())">
                            </span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <p class="item-subtotal">$<span x-text="fmt(item.subtotal)"></span></p>
                            <button @click="removeItem(item.id)" type="button"
                                    title="Quitar" class="item-delete">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Fila 2: stepper + precio unitario --}}
                    <div class="flex items-center justify-between mt-2">
                        <div class="item-stepper">
                            <button @click="adjustQty(item, -1)" type="button"
                                    class="item-stepper-btn">−</button>

                            <span x-show="!item._editQty"
                                  class="item-stepper-val"
                                  title="Clic para editar"
                                  @click="startEditQty(item)"
                                  x-text="item.cantidad"></span>

                            <input x-show="item._editQty"
                                   x-model="item._newQty"
                                   type="text"
                                   inputmode="decimal"
                                   class="item-stepper-input"
                                   @keydown.enter.prevent="commitQty(item)"
                                   @keydown.escape="item._editQty = false"
                                   @keydown.arrow-up.prevent="item._newQty = Math.max(0.001, parseFloat(item._newQty || 0) + 1)"
                                   @keydown.arrow-down.prevent="item._newQty = Math.max(0.001, parseFloat(item._newQty || 0) - 1)"
                                   @blur="commitQty(item)"
                                   x-effect="if (item._editQty) $nextTick(() => $el.select())">

                            <button @click="adjustQty(item, 1)" type="button"
                                    class="item-stepper-btn">+</button>
                        </div>

                        <p class="item-unit-price">$<span x-text="fmt(item.precio_unitario)"></span> c/u</p>
                    </div>

                    <p x-show="item._error" class="item-error" x-text="item._error"></p>

                </div>
            </template>

        </div>

        {{-- Footer: total + cobrar --}}
        <div class="shrink-0 px-5 py-4 border-t border-gray-100">

            @if(!empty($errors) && $errors->any())
            <div class="mb-3 space-y-1">
                @foreach($errors->all() as $error)
                <p class="text-xs text-red-500">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-400">Total</p>
                    <p class="text-2xl font-black text-gray-900 tabular-nums">
                        $<span x-text="fmt(total)"></span>
                    </p>
                </div>
                <button type="button"
                        @click="abrirCobro()"
                        :disabled="items.length === 0"
                        :class="items.length > 0
                            ? 'bg-green-600 hover:bg-green-700 text-white cursor-pointer shadow-lg shadow-green-600/20 active:scale-[.98]'
                            : 'bg-gray-100 text-gray-300 cursor-not-allowed'"
                        class="shrink-0 px-8 py-3 rounded-xl font-bold text-base transition-all">
                    <span x-show="items.length === 0">Sin productos</span>
                    <span x-show="items.length > 0" class="flex items-center gap-2">
                        Cobrar
                        <span class="text-white/60 text-xs font-normal">F12</span>
                    </span>
                </button>
            </div>
        </div>

    </div>

    {{-- ── Modal producto libre ──────────────────────────────────────────── --}}
    <div x-show="ventaLibre.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">

        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="ventaLibre.open = false"></div>

        <div x-show="ventaLibre.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xs p-5 space-y-4">

            <div class="flex items-center justify-between">
                <h2 class="font-bold text-gray-900 text-sm">Producto libre</h2>
                <button @click="ventaLibre.open = false" type="button"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Nombre</label>
                    <input type="text" x-model="ventaLibre.nombre" x-ref="libreNombre"
                           @keydown.enter.prevent="$refs.librePrecio.focus()"
                           placeholder="Ej: Mano de obra, flete…"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Precio unit.</label>
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                            <input type="number" x-model="ventaLibre.precio" x-ref="librePrecio"
                                   @keydown.enter.prevent="$refs.libreCantidad.focus()"
                                   step="0.01" min="0.01" placeholder="0.00"
                                   class="w-full border border-gray-200 rounded-lg pl-6 pr-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Cantidad</label>
                        <input type="number" x-model="ventaLibre.cantidad" x-ref="libreCantidad"
                               step="1" min="0.001" placeholder="1"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                    </div>
                </div>
                <div x-show="ventaLibre.precio > 0 && ventaLibre.cantidad > 0"
                     class="text-xs text-gray-500 text-right">
                    Subtotal: <span class="font-bold text-gray-700">
                        $<span x-text="fmt(parseFloat(ventaLibre.precio || 0) * parseFloat(ventaLibre.cantidad || 0))"></span>
                    </span>
                </div>
            </div>

            <button @click="addLibre()" type="button"
                    :disabled="!ventaLibre.nombre.trim() || ventaLibre.precio <= 0 || ventaLibre.cantidad <= 0"
                    :class="ventaLibre.nombre.trim() && ventaLibre.precio > 0 && ventaLibre.cantidad > 0
                        ? 'bg-violet-600 hover:bg-violet-700 text-white cursor-pointer'
                        : 'bg-gray-100 text-gray-300 cursor-not-allowed'"
                    class="w-full py-2.5 rounded-xl font-semibold text-sm transition-all">
                Agregar al ticket
            </button>
        </div>
    </div>

    {{-- ── Modal de búsqueda/escaneo ──────────────────────────────────── --}}
    <div x-show="buscando"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">

        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="cerrarBuscador()"></div>

        <div x-show="buscando"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col"
             style="max-height: 75vh">

            {{-- Input --}}
            <div class="px-4 py-3.5 border-b border-gray-100 flex items-center gap-3 shrink-0">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v3m0 8v3a2 2 0 002 2h2m8-18h2a2 2 0 012 2v3m0 8v3a2 2 0 01-2 2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5h6"/>
                </svg>
                <input type="text"
                       x-model="query"
                       x-ref="modalSearchInput"
                       @input="onSearch()"
                       @keydown.enter.prevent="onEnter()"
                       @keydown.escape="cerrarBuscador()"
                       @keydown.arrow-down.prevent="highlighted = Math.min(highlighted + 1, results.length - 1)"
                       @keydown.arrow-up.prevent="highlighted = Math.max(highlighted - 1, -1)"
                       placeholder="Escanea el código o escribe el nombre..."
                       autocomplete="off"
                       class="flex-1 text-sm text-gray-900 placeholder-gray-400 focus:outline-none">
                <button @click="query = ''; results = []; scanFlash = null; cancelAdd()" x-show="query" type="button"
                        class="shrink-0 text-gray-300 hover:text-gray-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <button @click="cerrarBuscador()" type="button"
                        class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Flash: éxito --}}
            <div x-show="scanFlash === 'ok'"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mx-4 mt-3 shrink-0 flex items-center gap-2 bg-green-50 border border-green-100 rounded-xl px-4 py-2.5">
                <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-sm font-semibold text-green-700" x-text="scanMsg"></span>
            </div>

            {{-- Flash: error --}}
            <div x-show="scanFlash === 'error'"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mx-4 mt-3 shrink-0 flex items-center gap-2 bg-red-50 border border-red-100 rounded-xl px-4 py-2.5">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="text-sm font-semibold text-red-600" x-text="scanMsg"></span>
            </div>

            {{-- Panel de agregar (presentaciones + cantidad) --}}
            <div x-show="adding && selected"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="px-4 pt-4 pb-3 border-b border-gray-100 shrink-0">

                <div class="flex items-start justify-between mb-3 gap-2">
                    <p class="font-semibold text-gray-900 text-sm leading-snug truncate" x-text="selected?.name"></p>
                    <button @click="cancelAdd()" type="button"
                            class="shrink-0 text-gray-300 hover:text-gray-500 transition-colors mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="selected?.presentations?.length > 0" class="mb-3">
                    <div class="grid grid-cols-2 gap-1.5">
                        <template x-for="p in (selected?.presentations ?? [])" :key="p.id">
                            <button type="button" @click="presId = p.id"
                                    :class="presId == p.id
                                        ? 'border-brand-700 bg-brand-700 text-white'
                                        : 'border-gray-200 text-gray-700 hover:border-brand-300'"
                                    class="border rounded-lg px-3 py-2 text-left transition-colors">
                                <p class="text-xs font-semibold truncate" x-text="p.nombre"></p>
                                <p class="text-xs opacity-70 tabular-nums mt-0.5">$<span x-text="fmt(p.precio)"></span></p>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="selected && !selected?.presentations?.length" class="mb-3">
                    <p class="text-xs text-gray-400">
                        Precio: <span class="font-bold text-gray-700">$<span x-text="fmt(selected?.price ?? 0)"></span></span>
                    </p>
                </div>

                {{-- Código de color (igualaciones) --}}
                <div class="flex items-center gap-2 mb-2 bg-violet-50 rounded-lg px-3 py-2">
                    <svg class="w-3.5 h-3.5 text-violet-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    <input type="text" x-model="codigoColor"
                           placeholder="Código de color (opcional)"
                           class="flex-1 text-xs font-medium text-violet-700 placeholder-violet-300 bg-transparent focus:outline-none">
                </div>

                <div class="flex items-center gap-2">
                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white">
                        <button type="button" @click="cantidad = Math.max(1, parseFloat(cantidad) - 1)"
                                class="px-3 py-2 text-gray-400 hover:text-gray-700 hover:bg-gray-50 transition-colors text-sm font-bold select-none">−</button>
                        <input type="number" x-model="cantidad" min="0.001" step="1"
                               class="w-12 text-center py-2 text-sm font-semibold focus:outline-none">
                        <button type="button" @click="cantidad = parseFloat(cantidad) + 1"
                                class="px-3 py-2 text-gray-400 hover:text-gray-700 hover:bg-gray-50 transition-colors text-sm font-bold select-none">+</button>
                    </div>
                    <button @click="addItem()" type="button"
                            class="flex-1 bg-brand-700 hover:bg-brand-800 text-white text-sm font-bold py-2 rounded-lg transition-colors text-center">
                        Agregar · $<span x-text="addSubtotal"></span>
                    </button>
                </div>

            </div>

            {{-- Resultados --}}
            <div x-show="!adding" class="flex-1 overflow-y-auto min-h-0">

                <div x-show="loading" class="flex items-center justify-center py-10">
                    <svg class="w-5 h-5 animate-spin text-gray-300" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </div>

                <div x-show="!loading && !query && results.length === 0"
                     class="flex flex-col items-center justify-center text-center gap-3 px-6 py-14">
                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v3m0 8v3a2 2 0 002 2h2m8-18h2a2 2 0 012 2v3m0 8v3a2 2 0 01-2 2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5h6"/>
                    </svg>
                    <p class="text-sm text-gray-400 font-medium">Escanea un código o escribe el nombre del producto</p>
                </div>

                <div x-show="!loading && query && results.length === 0 && !scanFlash"
                     class="py-12 text-center px-6">
                    <p class="text-sm text-gray-400">Sin resultados para "<span x-text="query" class="text-gray-600 font-medium"></span>"</p>
                </div>

                <div x-show="!loading && results.length > 0">
                    <template x-for="(product, i) in results" :key="product.id">
                        <button type="button" @click="selectProduct(product)"
                                :class="i === highlighted ? 'bg-brand-50 border-l-2 border-brand-600' : 'hover:bg-gray-50'"
                                @mouseenter="highlighted = i"
                                class="w-full flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 text-left transition-colors">
                            <div class="w-8 h-8 rounded-lg shrink-0 opacity-80"
                                 :style="`background-color: ${product.color}`"></div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-gray-900 truncate" x-text="product.name"></p>
                                <p class="text-xs text-gray-400 tabular-nums mt-0.5"
                                   x-text="product.presentations.length
                                       ? `${product.presentations.length} presentación(es)`
                                       : `$${fmt(product.price)}`"></p>
                            </div>
                            <span class="text-xs tabular-nums font-medium shrink-0"
                                  :class="product.stock_litros > 0 ? 'text-gray-400' : 'text-red-400'"
                                  x-text="stockFmt(product.stock_litros, product.unit)"></span>
                        </button>
                    </template>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Modal entrada/salida de caja ─────────────────────────────────── --}}
    <div x-show="movimientoCaja.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">

        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
             @click="movimientoCaja.open = false"></div>

        <div x-show="movimientoCaja.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-effect="if (movimientoCaja.open) $nextTick(() => $refs.movConcepto?.focus())"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xs p-5 space-y-4">

            <div class="flex items-center justify-between">
                <h2 class="font-bold text-gray-900"
                    x-text="movimientoCaja.tipo === 'entrada' ? '↑ Entrada de dinero' : '↓ Salida de dinero'"></h2>
                <button @click="movimientoCaja.open = false" type="button"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @if($active)
            <form method="POST" action="{{ route('empleados.pos.movimiento-caja', $active) }}" class="space-y-3">
                @csrf
                <input type="hidden" name="tipo" :value="movimientoCaja.tipo">

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Concepto</label>
                    <input type="text" name="concepto" required maxlength="200"
                           placeholder="Ej: Pago a proveedor"
                           x-ref="movConcepto"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Monto</label>
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                        <input type="number" name="monto" step="0.01" min="0.01" required
                               class="w-full border border-gray-200 rounded-lg pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                    </div>
                </div>

                <button type="submit"
                        :class="movimientoCaja.tipo === 'entrada'
                            ? 'bg-teal-600 hover:bg-teal-700'
                            : 'bg-red-600 hover:bg-red-700'"
                        class="w-full text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    <span x-text="movimientoCaja.tipo === 'entrada' ? 'Registrar entrada' : 'Registrar salida'"></span>
                </button>
            </form>
            @else
            <p class="text-sm text-gray-500 text-center py-4">Abre un ticket primero para registrar movimientos.</p>
            @endif
        </div>
    </div>

    {{-- ── Modal de cobro ──────────────────────────────────────────────── --}}
    <div x-show="cobrando"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">

        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="cerrarCobro()"></div>

        <div x-show="cobrando"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm space-y-5 p-6">

            <div class="flex items-center justify-between">
                <h2 class="font-bold text-gray-900">Cobrar venta</h2>
                <button @click="cerrarCobro()" type="button"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Total + descuento --}}
            <div class="bg-gray-50 rounded-xl px-4 py-3 space-y-1.5">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide"
                          x-text="_descuentoMonto > 0 ? 'Subtotal' : 'Total'"></span>
                    <span class="text-2xl font-black text-gray-900 tabular-nums">
                        $<span x-text="fmt(total)"></span>
                    </span>
                </div>
                <div x-show="_descuentoMonto > 0" class="flex justify-between items-center text-sm">
                    <span class="text-red-500 font-medium">
                        Descuento
                        <span x-show="_descuentoTipo === 'porcentaje'"
                              x-text="`(${_descuentoValor}%)`"
                              class="text-xs"></span>
                    </span>
                    <span class="font-bold text-red-500 tabular-nums">
                        − $<span x-text="fmt(_descuentoMonto)"></span>
                    </span>
                </div>
                <div x-show="_descuentoMonto > 0"
                     class="flex justify-between items-center border-t border-gray-200 pt-1.5">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total a cobrar</span>
                    <span class="text-xl font-black text-brand-700 tabular-nums">
                        $<span x-text="fmt(_totalFinal)"></span>
                    </span>
                </div>
            </div>

            {{-- Descuento (opcional) --}}
            <div x-data="{ abierto: false }">
                <button @click="abierto = !abierto" type="button"
                        class="w-full flex items-center gap-2 text-xs transition-colors"
                        :class="_descuentoMonto > 0 ? 'text-red-500 hover:text-red-600' : 'text-gray-400 hover:text-gray-600'">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span x-text="_descuentoMonto > 0
                        ? 'Descuento aplicado: −$' + fmt(_descuentoMonto)
                        : 'Aplicar descuento (opcional)'"></span>
                    <svg class="w-3 h-3 ml-auto transition-transform" :class="abierto ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="abierto" x-transition class="mt-2 flex items-center gap-2">
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden text-xs shrink-0">
                        <button @click="_descuentoTipo = 'porcentaje'" type="button"
                                :class="_descuentoTipo === 'porcentaje' ? 'bg-brand-700 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
                                class="px-2.5 py-1.5 font-semibold transition-colors">%</button>
                        <button @click="_descuentoTipo = 'fijo'" type="button"
                                :class="_descuentoTipo === 'fijo' ? 'bg-brand-700 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
                                class="px-2.5 py-1.5 font-semibold transition-colors border-l border-gray-200">$</button>
                    </div>
                    <div class="relative flex-1">
                        <input type="text" inputmode="decimal"
                               x-model="_descuentoValor"
                               :placeholder="_descuentoTipo === 'porcentaje' ? 'Ej: 10' : 'Ej: 50.00'"
                               class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                    </div>
                    <button @click="_descuentoValor = ''" type="button"
                            x-show="_descuentoValor"
                            class="shrink-0 text-xs text-gray-400 hover:text-red-500 transition-colors">
                        Quitar
                    </button>
                </div>
            </div>

            {{-- Tipo de pago --}}
            <div class="grid grid-cols-3 gap-1 bg-gray-100 p-1 rounded-xl">
                <button @click="_pagoTipo = 'efectivo'" type="button"
                        :class="_pagoTipo === 'efectivo' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                        class="py-2 rounded-lg text-xs transition-all">Efectivo</button>
                <button @click="_pagoTipo = 'tarjeta'" type="button"
                        :class="_pagoTipo === 'tarjeta' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                        class="py-2 rounded-lg text-xs transition-all">Tarjeta</button>
                <button @click="_pagoTipo = 'transferencia'" type="button"
                        :class="_pagoTipo === 'transferencia' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                        class="py-2 rounded-lg text-xs transition-all">Transf.</button>
            </div>

            {{-- ── MODO SIMPLE (por defecto) ────────────────────────────────── --}}
            <div x-show="!_multiPago">

                {{-- Efectivo --}}
                <div x-show="_pagoTipo === 'efectivo'" class="space-y-3">
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xl">$</span>
                        <input type="text" inputmode="decimal"
                               x-model="_pagoMonto"
                               x-ref="pagoMontoInput"
                               placeholder="0.00"
                               class="w-full border-2 border-gray-200 focus:border-brand-700 rounded-xl pl-9 pr-4 py-4 text-3xl font-black tabular-nums text-gray-900 focus:outline-none transition-colors">
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <button @click="_pagoMonto = _totalFinal.toFixed(2)" type="button"
                                class="py-1.5 px-3 bg-brand-50 hover:bg-brand-100 text-brand-700 rounded-lg text-xs font-semibold transition-colors">
                            Exacto
                        </button>
                        @foreach ([50, 100, 200, 500, 1000] as $monto)
                        <button @click="_pagoMonto = '{{ $monto }}'" type="button"
                                class="py-1.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-colors">
                            ${{ number_format($monto) }}
                        </button>
                        @endforeach
                    </div>
                    <div x-show="_cambio > 0"
                         class="flex justify-between items-center bg-green-50 border border-green-100 rounded-xl px-4 py-3">
                        <span class="font-semibold text-green-700">Cambio</span>
                        <span class="text-2xl font-black text-green-700 tabular-nums">
                            $<span x-text="fmt(_cambio)"></span>
                        </span>
                    </div>
                </div>

                {{-- Tarjeta / transferencia --}}
                <div x-show="_pagoTipo !== 'efectivo'" class="space-y-2">
                    <div class="bg-gray-50 rounded-xl px-4 py-3 flex justify-between items-center">
                        <span class="text-xs text-gray-400">Monto</span>
                        <span class="font-bold text-gray-900 tabular-nums">$<span x-text="fmt(_totalFinal)"></span></span>
                    </div>
                    <input type="text" x-model="_pagoRef"
                           placeholder="Referencia / autorización (opcional)"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                </div>

                {{-- Opción multi-pago --}}
                <button @click="_multiPago = true; pagos = []" type="button"
                        class="mt-3 w-full text-center text-xs text-gray-400 hover:text-brand-600 transition-colors">
                    + Dividir en varios métodos de pago
                </button>
            </div>

            {{-- ── MODO MULTI-PAGO (opcional) ───────────────────────────────── --}}
            <div x-show="_multiPago" class="space-y-2">

                {{-- Input monto + agregar --}}
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                        <input type="text" inputmode="decimal"
                               x-model="_pagoMonto"
                               placeholder="0.00"
                               @keydown.enter.prevent="agregarPago()"
                               class="w-full border-2 border-gray-200 focus:border-brand-700 rounded-xl pl-8 pr-3 py-2.5 text-lg font-black tabular-nums text-gray-900 focus:outline-none transition-colors">
                    </div>
                    <button @click="agregarPago()" type="button"
                            class="shrink-0 bg-brand-700 hover:bg-brand-800 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors">
                        + Agregar
                    </button>
                </div>

                {{-- Atajos efectivo --}}
                <div x-show="_pagoTipo === 'efectivo'" class="flex flex-wrap gap-1.5">
                    <button @click="_pagoMonto = (_restante > 0 ? _restante : total).toFixed(2)" type="button"
                            class="py-1 px-2.5 bg-brand-50 hover:bg-brand-100 text-brand-700 rounded-lg text-xs font-semibold transition-colors">
                        Exacto
                    </button>
                    @foreach ([50, 100, 200, 500, 1000] as $monto)
                    <button @click="_pagoMonto = '{{ $monto }}'" type="button"
                            class="py-1 px-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-colors">
                        ${{ number_format($monto) }}
                    </button>
                    @endforeach
                </div>

                {{-- Referencia tarjeta/transf --}}
                <input x-show="_pagoTipo !== 'efectivo'"
                       type="text" x-model="_pagoRef"
                       placeholder="Referencia / autorización (opcional)"
                       @keydown.enter.prevent="agregarPago()"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">

                {{-- Lista de pagos --}}
                <template x-for="(pago, i) in pagos" :key="i">
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl px-3 py-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-xs font-semibold text-gray-500 capitalize shrink-0" x-text="pago.tipo"></span>
                            <span x-show="pago.referencia" class="text-xs text-gray-400 truncate" x-text="'· ' + pago.referencia"></span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="font-bold text-gray-900 tabular-nums text-sm">$<span x-text="fmt(pago.monto)"></span></span>
                            <button @click="quitarPago(i)" type="button"
                                    class="w-5 h-5 flex items-center justify-center rounded-md text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Resumen --}}
                <div x-show="pagos.length > 0" class="border-t border-gray-100 pt-2 space-y-1">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Total pagado</span>
                        <span class="font-semibold tabular-nums"
                              :class="_totalPagado >= total ? 'text-green-600' : 'text-gray-700'">
                            $<span x-text="fmt(_totalPagado)"></span>
                        </span>
                    </div>
                    <div x-show="_restante > 0" class="flex justify-between text-xs">
                        <span class="text-gray-400">Restante</span>
                        <span class="font-semibold text-red-500 tabular-nums">$<span x-text="fmt(_restante)"></span></span>
                    </div>
                    <div x-show="_cambio > 0"
                         class="flex justify-between items-center bg-green-50 border border-green-100 rounded-xl px-3 py-2">
                        <span class="text-sm font-semibold text-green-700">Cambio</span>
                        <span class="font-black text-green-700 tabular-nums">$<span x-text="fmt(_cambio)"></span></span>
                    </div>
                </div>

                {{-- Volver a modo simple --}}
                <button @click="_multiPago = false; pagos = []; _pagoMonto = total.toFixed(2)" type="button"
                        class="w-full text-center text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    ← Pago único
                </button>
            </div>

            {{-- Cliente (opcional) --}}
            <div x-data="{ open: false }">
                <button @click="open = !open" type="button"
                        class="w-full flex items-center gap-2 text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span x-text="(clienteNombre || clienteTelefono)
                        ? 'Cliente: ' + (clienteNombre || clienteTelefono)
                        : 'Agregar cliente (opcional)'"></span>
                    <svg class="w-3 h-3 ml-auto transition-transform" :class="open ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition class="mt-2 grid grid-cols-2 gap-2">
                    <input type="text" x-model="clienteNombre" placeholder="Nombre"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                    <input type="text" x-model="clienteTelefono" placeholder="Teléfono"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                </div>
            </div>

            @if(!empty($errors) && $errors->any())
            <div class="space-y-1">
                @foreach($errors->all() as $error)
                <p class="text-xs text-red-500">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('empleados.pos.confirmar', $active) }}" method="POST" x-ref="confirmForm">
                @csrf
                <input type="hidden" name="cliente_nombre"   x-model="clienteNombre">
                <input type="hidden" name="cliente_telefono" x-model="clienteTelefono">
                <input type="hidden" name="vendedor"         x-model="vendedor">
                <input type="hidden" name="descuento_tipo"   :value="_descuentoMonto > 0 ? _descuentoTipo : ''">
                <input type="hidden" name="descuento_valor"  :value="_descuentoMonto > 0 ? _descuentoValor : '0'">
                {{-- Modo simple: un solo pago --}}
                <template x-if="!_multiPago">
                    <span>
                        <input type="hidden" name="pagos[0][tipo]"       :value="_pagoTipo">
                        <input type="hidden" name="pagos[0][monto]"      :value="_pagoTipo === 'efectivo' ? parseFloat(String(_pagoMonto).replace(',','.') || 0) : total">
                        <input type="hidden" name="pagos[0][referencia]" :value="_pagoRef">
                    </span>
                </template>
                {{-- Modo multi: inputs dinámicos --}}
                <template x-if="_multiPago">
                    <template x-for="(pago, i) in pagos" :key="i">
                        <span>
                            <input type="hidden" :name="`pagos[${i}][tipo]`"       :value="pago.tipo">
                            <input type="hidden" :name="`pagos[${i}][monto]`"      :value="pago.monto">
                            <input type="hidden" :name="`pagos[${i}][referencia]`" :value="pago.referencia">
                        </span>
                    </template>
                </template>
                <button type="button"
                        @click="puedeConfirmar && $refs.confirmForm.submit()"
                        :disabled="!puedeConfirmar"
                        :class="puedeConfirmar
                            ? 'bg-green-600 hover:bg-green-700 text-white cursor-pointer shadow-lg shadow-green-600/20'
                            : 'bg-gray-100 text-gray-300 cursor-not-allowed'"
                        class="w-full font-bold py-3.5 rounded-xl transition-all text-sm">
                    <span x-show="!puedeConfirmar"
                          x-text="_multiPago && _restante > 0 ? 'Falta $' + fmt(_restante) : 'Ingresa el monto recibido'"></span>
                    <span x-show="puedeConfirmar">✓ Confirmar venta</span>
                </button>
            </form>

            <a href="{{ route('empleados.pos.cotizacion', $active) }}" target="_blank"
               class="block w-full text-center text-xs text-gray-400 hover:text-brand-600 transition-colors pt-1 pb-0.5">
                Imprimir cotización sin cobrar →
            </a>

        </div>
    </div>
    @endif

</div>

<script>
function pos(cfg) {
    return {
        ventaId:      cfg.ventaId,
        sucursalId:   cfg.sucursalId,
        items:        cfg.itemsInit.map(i => ({ ...i, _editQty: false, _newQty: 0, _error: '' })),
        total:        cfg.totalInit,
        unitAbbrs:    cfg.unitAbbrs,
        decimalUnits: cfg.decimalUnits,
        stockFmt(qty, unit) {
            const isDecimal = this.decimalUnits.includes(unit);
            const val = isDecimal ? parseFloat(qty).toFixed(1) : Math.round(qty).toString();
            return val + ' ' + (this.unitAbbrs[unit] ?? unit);
        },

        // Búsqueda
        query:        '',
        results:      [],
        loading:      false,
        highlighted:  -1,
        _searchTimer: null,

        // Panel de agregar
        adding:      false,
        selected:    null,
        presId:      null,
        cantidad:    1,
        codigoColor: '',

        // Modal búsqueda
        buscando:   false,
        scanFlash:  null,   // 'ok' | 'error' | null
        scanMsg:    '',
        _scanTimer: null,

        // Buffer para scanner cuando el modal está cerrado
        _scanBuffer:     '',
        _scanBufTimeout: null,

        // Producto libre
        ventaLibre: { open: false, nombre: '', precio: '', cantidad: 1 },

        // Movimiento de caja
        movimientoCaja: { open: false, tipo: 'entrada' },

        // Vendedor
        vendedor: '',

        // Cobro
        clienteNombre:    '',
        clienteTelefono:  '',
        cobrando:         false,
        _multiPago:       false,
        pagos:            [],
        _pagoTipo:        'efectivo',
        _pagoMonto:       '',
        _pagoRef:         '',

        // Descuento
        _descuentoTipo:   'porcentaje',   // 'porcentaje' | 'fijo'
        _descuentoValor:  '',

        get _descuentoMonto() {
            const v = parseFloat(String(this._descuentoValor).replace(',', '.') || 0);
            if (!v || v <= 0) return 0;
            if (this._descuentoTipo === 'porcentaje') {
                return Math.round(this.total * Math.min(v, 100) / 100 * 100) / 100;
            }
            return Math.round(Math.min(v, this.total) * 100) / 100;
        },
        get _totalFinal() {
            return Math.max(0, Math.round((this.total - this._descuentoMonto) * 100) / 100);
        },

        get _totalPagado() {
            return Math.round(this.pagos.reduce((s, p) => s + p.monto, 0) * 100) / 100;
        },
        get _restante() {
            return Math.max(0, Math.round((this._totalFinal - this._totalPagado) * 100) / 100);
        },
        get _cambio() {
            if (this._multiPago) {
                return Math.max(0, Math.round((this._totalPagado - this._totalFinal) * 100) / 100);
            }
            if (this._pagoTipo !== 'efectivo') return 0;
            const rec = parseFloat(String(this._pagoMonto).replace(',', '.') || 0);
            return Math.max(0, Math.round((rec - this._totalFinal) * 100) / 100);
        },
        get puedeConfirmar() {
            if (!this.items.length) return false;
            if (this._multiPago) return this.pagos.length > 0 && this._totalPagado >= this._totalFinal;
            if (this._pagoTipo === 'efectivo') {
                return parseFloat(String(this._pagoMonto).replace(',', '.') || 0) >= this._totalFinal;
            }
            return true;
        },
        get selectedPres() {
            if (!this.selected || !this.presId) return null;
            return this.selected.presentations.find(p => p.id == this.presId) ?? null;
        },
        get addSubtotal() {
            const price = this.selectedPres ? this.selectedPres.precio : (this.selected?.price ?? 0);
            return this.fmt(price * (parseFloat(this.cantidad) || 0));
        },

        fmt(n) {
            return Number(n || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        // ── Búsqueda ───────────────────────────────────────────────────

        abrirLibre() {
            this.ventaLibre = { open: true, nombre: '', precio: '', cantidad: 1 };
            this.$nextTick(() => this.$refs.libreNombre?.focus());
        },

        async addLibre() {
            const nombre   = (this.ventaLibre.nombre || '').trim();
            const precio   = parseFloat(this.ventaLibre.precio);
            const cantidad = parseFloat(this.ventaLibre.cantidad);
            if (!nombre || isNaN(precio) || precio <= 0 || isNaN(cantidad) || cantidad <= 0) return;

            const body = new FormData();
            body.append('_token', cfg.csrf);
            body.append('nombre',   nombre);
            body.append('precio',   precio);
            body.append('cantidad', cantidad);

            const r = await fetch(`/empleados/pos/${this.ventaId}/libre`, { method: 'POST', body });
            if (!r.ok) {
                const err = await r.json().catch(() => ({}));
                alert(err.error ?? 'Error al agregar');
                return;
            }
            const data      = await r.json();
            this.items      = this.normalizeItems(data.items);
            this.total      = data.total;
            this.ventaLibre = { open: false, nombre: '', precio: '', cantidad: 1 };
        },

        abrirBuscador() {
            this.buscando  = true;
            this.scanFlash = null;
            this.$nextTick(() => this.$refs.modalSearchInput?.focus());
        },

        cerrarBuscador() {
            this.buscando    = false;
            this.scanFlash   = null;
            this.query       = '';
            this.results     = [];
            this.highlighted = -1;
            clearTimeout(this._scanTimer);
            this.cancelAdd();
        },

        onSearch() {
            clearTimeout(this._searchTimer);
            this.highlighted = -1;
            if (!this.query.trim()) { this.results = []; return; }
            this._searchTimer = setTimeout(() => this._fetchResults(), 280);
        },

        // Llamado al presionar Enter — búsqueda inmediata + auto-agregar si hay coincidencia exacta
        async onEnter() {
            // Si hay un producto resaltado con flechas, usarlo directamente
            if (this.highlighted >= 0 && this.results[this.highlighted]) {
                const product = this.results[this.highlighted];
                this.highlighted = -1;
                if (product.presentations.length === 0) {
                    await this.autoAdd(product, null);
                } else if (product.presentations.length === 1) {
                    await this.autoAdd(product, product.presentations[0].id);
                } else {
                    this.selectProduct(product);
                }
                return;
            }

            const q = this.query.trim();
            if (!q) return;
            clearTimeout(this._searchTimer);
            this.loading   = true;
            this.scanFlash = null;
            try {
                const url = new URL(cfg.buscarUrl, window.location.origin);
                url.searchParams.set('q', q);
                if (this.sucursalId) url.searchParams.set('sucursal_id', this.sucursalId);
                const r = await fetch(url);
                this.results = await r.json();

                if (this.results.length === 0) {
                    this.scanFlash = 'error';
                    this.scanMsg   = `Producto "${q}" no encontrado`;
                    this._flashAuto(3000);
                    return;
                }

                // Coincidencia exacta por código de barras
                const exact = this.results.find(p => p.codigo_barras === q);
                const target = exact ?? (this.results.length === 1 ? this.results[0] : null);

                if (target) {
                    if (target.presentations.length === 0) {
                        await this.autoAdd(target, null);
                    } else if (target.presentations.length === 1) {
                        await this.autoAdd(target, target.presentations[0].id);
                    } else {
                        // Varias presentaciones: mostrar selector
                        this.selectProduct(target);
                    }
                    return;
                }

                // Múltiples resultados — mostrar lista
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        async _fetchResults() {
            if (!this.query.trim()) return;
            this.loading = true;
            try {
                const url = new URL(cfg.buscarUrl, window.location.origin);
                url.searchParams.set('q', this.query.trim());
                if (this.sucursalId) url.searchParams.set('sucursal_id', this.sucursalId);
                const r = await fetch(url);
                this.results = await r.json();
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        _flashAuto(ms = 2500) {
            clearTimeout(this._scanTimer);
            this._scanTimer = setTimeout(() => { this.scanFlash = null; }, ms);
        },

        // Auto-agregar desde escaneo (sin abrir panel de presentaciones)
        async autoAdd(product, presId) {
            const body = new FormData();
            body.append('_token', cfg.csrf);
            body.append('product_id', product.id);
            if (presId) body.append('product_presentation_id', presId);
            body.append('cantidad', 1);

            const r = await fetch(`/empleados/pos/${this.ventaId}/items`, { method: 'POST', body });

            if (!r.ok) {
                const err = await r.json().catch(() => ({}));
                this.scanFlash = 'error';
                this.scanMsg   = err.error ?? 'Error al agregar el producto';
                this._flashAuto();
                return;
            }

            const data = await r.json();
            this.items = this.normalizeItems(data.items);
            this.total = data.total;
            this.cerrarBuscador();
        },

        selectProduct(product) {
            this.selected     = product;
            this.presId       = product.presentations[0]?.id ?? null;
            this.cantidad     = 1;
            this.codigoColor  = '';
            this.adding       = true;
        },

        cancelAdd() {
            this.selected     = null;
            this.presId       = null;
            this.cantidad     = 1;
            this.codigoColor  = '';
            this.adding       = false;
        },

        // Agregar manualmente (desde panel de presentaciones)
        async addItem() {
            if (!this.selected || !this.ventaId) return;

            const body = new FormData();
            body.append('_token', cfg.csrf);
            body.append('product_id', this.selected.id);
            if (this.presId) body.append('product_presentation_id', this.presId);
            body.append('cantidad', this.cantidad);
            if (this.codigoColor.trim()) body.append('codigo_color', this.codigoColor.trim());

            const r = await fetch(`/empleados/pos/${this.ventaId}/items`, { method: 'POST', body });

            if (!r.ok) {
                const err = await r.json().catch(() => ({}));
                alert(err.error ?? 'Error al agregar el producto');
                return;
            }

            const data = await r.json();
            this.items = this.normalizeItems(data.items);
            this.total = data.total;
            this.cerrarBuscador();
        },

        async removeItem(itemId) {
            const r = await fetch(`/empleados/pos/${this.ventaId}/items/${itemId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': cfg.csrf },
            });
            if (!r.ok) return;
            const data = await r.json();
            this.items = this.normalizeItems(data.items);
            this.total = data.total;
        },

        // ── Editar cantidad ────────────────────────────────────────────

        normalizeItems(raw) {
            return raw.map(i => ({ ...i, _editQty: false, _newQty: 0, _error: '' }));
        },

        startEditQty(item) {
            item._newQty  = item.cantidad;
            item._error   = '';
            item._editQty = true;
        },

        async adjustQty(item, delta) {
            const newQty = Math.round((item.cantidad + delta) * 1000) / 1000;
            if (newQty <= 0) {
                await this.removeItem(item.id);
                return;
            }
            const r = await fetch(`/empleados/pos/${this.ventaId}/items/${item.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                body: JSON.stringify({ cantidad: newQty }),
            });
            const data = await r.json();
            if (!r.ok) {
                item._error = data.error ?? 'Stock insuficiente';
                return;
            }
            this.items = this.normalizeItems(data.items);
            this.total = data.total;
        },

        async commitQty(item) {
            if (!item._editQty) return;
            item._editQty = false;

            const newQty = parseFloat(String(item._newQty).replace(',', '.'));
            if (!newQty || newQty === item.cantidad) return;

            const r = await fetch(`/empleados/pos/${this.ventaId}/items/${item.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                body: JSON.stringify({ cantidad: newQty }),
            });

            const data = await r.json();
            if (!r.ok) {
                item._error   = data.error ?? 'Error al actualizar';
                item._editQty = false;
                return;
            }
            this.items = this.normalizeItems(data.items);
            this.total = data.total;
        },

        // ── Código de color inline ─────────────────────────────────────

        async saveCodigo(item, valor) {
            const r = await fetch(`/empleados/pos/${this.ventaId}/items/${item.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                body: JSON.stringify({ codigo_color: valor }),
            });
            if (!r.ok) return;
            const data = await r.json();
            this.items = this.normalizeItems(data.items);
            this.total = data.total;
        },

        // ── Cobro ──────────────────────────────────────────────────────

        abrirCobro() {
            this.cobrando         = true;
            this._multiPago       = false;
            this.pagos            = [];
            this._pagoTipo        = 'efectivo';
            this._pagoMonto       = this.total.toFixed(2);
            this._pagoRef         = '';
            this._descuentoTipo   = 'porcentaje';
            this._descuentoValor  = '';
            this.$nextTick(() => this.$refs.pagoMontoInput?.focus());
        },

        cerrarCobro() {
            this.cobrando = false;
        },

        agregarPago() {
            const monto = parseFloat(String(this._pagoMonto).replace(',', '.'));
            if (!monto || monto <= 0) return;
            this.pagos.push({
                tipo:      this._pagoTipo,
                monto:     Math.round(monto * 100) / 100,
                referencia: this._pagoRef.trim(),
            });
            this._pagoRef   = '';
            this._pagoMonto = this._restante > 0 ? this._restante.toFixed(2) : '';
            this.$nextTick(() => this.$refs.pagoMontoInput?.focus());
        },

        quitarPago(index) {
            this.pagos.splice(index, 1);
            this._pagoMonto = this._restante > 0 ? this._restante.toFixed(2) : '';
        },

        // ── Init ───────────────────────────────────────────────────────

        init() {
            // Captura input del scanner cuando el modal de búsqueda está cerrado
            document.addEventListener('keydown', (e) => {
                // F6 nuevo ticket
                if (e.key === 'F6') {
                    e.preventDefault();
                    document.getElementById('btn-nuevo-ticket')?.click();
                    return;
                }

                // F7 entrada de dinero
                if (e.key === 'F7') {
                    e.preventDefault();
                    if (this.ventaId && !this.cobrando && !this.buscando) {
                        this.movimientoCaja = { open: true, tipo: 'entrada' };
                    }
                    return;
                }

                // F8 salida de dinero
                if (e.key === 'F8') {
                    e.preventDefault();
                    if (this.ventaId && !this.cobrando && !this.buscando) {
                        this.movimientoCaja = { open: true, tipo: 'salida' };
                    }
                    return;
                }

                // Ctrl+P producto libre
                if (e.key === 'p' && e.ctrlKey) {
                    e.preventDefault();
                    if (this.ventaId && !this.cobrando && !this.buscando) {
                        this.abrirLibre();
                    }
                    return;
                }

                // F10 abre/cierra el buscador
                if (e.key === 'F10') {
                    e.preventDefault();
                    if (!this.cobrando && this.ventaId) {
                        this.buscando ? this.cerrarBuscador() : this.abrirBuscador();
                    }
                    return;
                }

                // F12 abre el cobro
                if (e.key === 'F12') {
                    e.preventDefault();
                    if (!this.buscando && this.ventaId && this.items.length > 0) {
                        this.abrirCobro();
                    }
                    return;
                }

                // Dejar que los inputs manejen sus propias teclas
                const tag = document.activeElement?.tagName;
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;
                if (this.cobrando || !this.ventaId) return;

                if (e.key === 'Enter' && this._scanBuffer.trim()) {
                    const code = this._scanBuffer;
                    this._scanBuffer = '';
                    clearTimeout(this._scanBufTimeout);
                    if (!this.buscando) this.buscando = true;
                    this.$nextTick(() => {
                        this.query = code;
                        this.onEnter();
                    });
                    e.preventDefault();
                    return;
                }

                if (e.key.length === 1 && !e.metaKey && !e.ctrlKey && !e.altKey) {
                    this._scanBuffer += e.key;
                    clearTimeout(this._scanBufTimeout);
                    // Si llegan caracteres pero sin Enter (ej: el usuario hizo clic en otro lugar),
                    // abrir el modal con lo que se acumuló
                    this._scanBufTimeout = setTimeout(() => {
                        if (this._scanBuffer && !this.buscando) {
                            const buf = this._scanBuffer;
                            this._scanBuffer = '';
                            this.buscando = true;
                            this.$nextTick(() => {
                                this.query = buf;
                                this.onSearch();
                            });
                        }
                        this._scanBuffer = '';
                    }, 300);
                }
            });
        },
    };
}
</script>
@endsection
