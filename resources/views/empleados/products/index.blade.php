@extends('layouts.empleados')

@section('title', 'Productos — IPESA SM')
@section('page-title', 'Productos')

@section('content')
<div class="space-y-6"
     x-data="{
         almacenes: {{ $sucursales->mapWithKeys(fn($s) => [$s->id => true])->toJson() }}
     }">

    <div class="flex flex-col sm:flex-row items-center gap-3">
        <form method="GET" action="{{ route('empleados.products.index') }}" class="flex-1 flex items-center gap-2">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por nombre o código de barras..."
                       class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors bg-white">
            </div>
            <button type="submit" class="shrink-0 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">Buscar</button>
            @if(request('search'))
            <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}"
               class="shrink-0 text-sm text-gray-400 hover:text-gray-600 transition-colors">✕</a>
            @endif
        </form>
        <p class="shrink-0 text-sm text-gray-500">{{ $products->total() }} producto(s)</p>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('empleados.products.create') }}"
           class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo producto
        </a>
        @endif
    </div>

    {{-- Filtro de almacenes --}}
    @if($sucursales->isNotEmpty())
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide mr-1">Stock de:</span>
        @foreach($sucursales as $sucursal)
        <label class="inline-flex items-center gap-1.5 cursor-pointer select-none"
               :class="almacenes[{{ $sucursal->id }}] ? 'opacity-100' : 'opacity-40'">
            <input type="checkbox" x-model="almacenes[{{ $sucursal->id }}]" class="w-3.5 h-3.5 accent-brand-700">
            <span class="text-xs text-gray-600">{{ $sucursal->nombre }}</span>
        </label>
        @endforeach
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if($products->isEmpty())
        <div class="py-20 text-center text-gray-400">
            <div class="text-5xl mb-3">📦</div>
            <p class="font-medium">No hay productos aún</p>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('empleados.products.create') }}" class="mt-4 inline-block text-sm text-brand-700 hover:underline">Crear el primero</a>
            @endif
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                @php
                    $sortIcon = function(string $col) use ($sort, $direction): string {
                        if ($sort !== $col) {
                            return '<svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>';
                        }
                        return $direction === 'asc'
                            ? '<svg class="w-3 h-3 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>'
                            : '<svg class="w-3 h-3 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>';
                    };
                    $sortUrl = fn(string $col) => request()->fullUrlWithQuery([
                        'sort'      => $col,
                        'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc',
                        'page'      => null,
                    ]);
                @endphp
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3">
                            <a href="{{ $sortUrl('name') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide {{ $sort === 'name' ? 'text-brand-700' : 'text-gray-500 hover:text-gray-700' }} transition-colors">
                                Producto {!! $sortIcon('name') !!}
                            </a>
                        </th>
                        <th class="px-6 py-3">
                            <a href="{{ $sortUrl('category') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide {{ $sort === 'category' ? 'text-brand-700' : 'text-gray-500 hover:text-gray-700' }} transition-colors">
                                Categoría {!! $sortIcon('category') !!}
                            </a>
                        </th>
                        <th class="px-6 py-3">
                            <a href="{{ $sortUrl('price') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide {{ $sort === 'price' ? 'text-brand-700' : 'text-gray-500 hover:text-gray-700' }} transition-colors">
                                Precio {!! $sortIcon('price') !!}
                            </a>
                        </th>
                        <th class="px-6 py-3">
                            <a href="{{ $sortUrl('coverage') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide {{ $sort === 'coverage' ? 'text-brand-700' : 'text-gray-500 hover:text-gray-700' }} transition-colors">
                                Rinde {!! $sortIcon('coverage') !!}
                            </a>
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock almacenes</th>
                        <th class="px-6 py-3">
                            <a href="{{ $sortUrl('active') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide {{ $sort === 'active' ? 'text-brand-700' : 'text-gray-500 hover:text-gray-700' }} transition-colors">
                                Estado {!! $sortIcon('active') !!}
                            </a>
                        </th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($products as $product)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm shrink-0"
                                     style="background-color: {{ $product->category->color }}">🪣</div>
                                <div>
                                    <p class="font-semibold text-sm text-gray-900">{{ $product->name }}</p>
                                    @if($product->featured)
                                    <span class="text-xs text-amber-600 font-medium">⭐ Destacado</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $product->category->color }}"></span>
                                {{ $product->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-sm text-gray-900">${{ number_format($product->price, 2) }}</span>
                            <span class="text-xs text-gray-400">/ {{ $product->unit }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $product->coverage }} m²/L</td>

                        {{-- Stock por almacén (columna única) --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-0.5">
                                @foreach($sucursales as $sucursal)
                                @php $stock = $product->inventario->firstWhere('sucursal_id', $sucursal->id)?->stock_litros; @endphp
                                <div class="flex items-center justify-between gap-2 text-xs"
                                     x-show="almacenes[{{ $sucursal->id }}]">
                                    <span class="text-gray-400 truncate">{{ $sucursal->nombre }}</span>
                                    @if($stock !== null)
                                        <span class="font-semibold tabular-nums shrink-0 whitespace-nowrap {{ $stock > 0 ? 'text-gray-700' : 'text-red-400' }}">
                                            {{ number_format($stock, \App\Support\Units::decimals($product->unit ?? 'litro')) }} {{ \App\Support\Units::abbr($product->unit ?? 'litro') }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 shrink-0">—</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            @if($product->active)
                            <span class="bg-green-50 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">Activo</span>
                            @else
                            <span class="bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full">Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1 justify-end">
                                <a href="{{ route('empleados.products.show', $product) }}"
                                   title="Ver detalle"
                                   class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('empleados.products.edit', $product) }}"
                                   title="Editar"
                                   class="w-8 h-8 flex items-center justify-center rounded-lg text-brand-700 hover:text-brand-800 hover:bg-brand-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @if(auth()->user()->isAdmin())
                                <form action="{{ route('empleados.products.destroy', $product) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar este producto?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Eliminar"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
