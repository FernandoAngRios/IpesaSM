@extends('layouts.empleados')

@section('title', $product->name . ' — IPESA SM')
@section('page-title', 'Detalle de producto')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <a href="{{ route('empleados.products.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="font-bold text-gray-900 text-lg">{{ $product->name }}</h2>
                    @if($product->featured)
                    <span class="text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">Destacado</span>
                    @endif
                    @if($product->active)
                    <span class="text-xs font-semibold text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded-full">Activo</span>
                    @else
                    <span class="text-xs font-semibold text-gray-500 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded-full">Inactivo</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $product->category->color }}"></span>
                        {{ $product->category->name }}
                    </span>
                    @if($product->codigo_barras)
                    <span class="ml-3 font-mono text-gray-400">{{ $product->codigo_barras }}</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('empleados.products.edit', $product) }}"
               class="shrink-0 text-sm font-semibold text-brand-700 hover:text-brand-800 px-4 py-2 rounded-xl hover:bg-brand-50 border border-brand-200 transition-colors">
                Editar
            </a>
        </div>

        {{-- Imagen --}}
        @if($product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image))
        <div class="border-b border-gray-100 flex justify-center bg-gray-50 py-4">
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}"
                 alt="{{ $product->name }}"
                 class="max-h-48 max-w-xs object-contain rounded-xl shadow-sm">
        </div>
        @endif

        {{-- Info general --}}
        <div class="px-6 py-5 grid sm:grid-cols-2 gap-x-8 gap-y-4">
            @if($product->short_description)
            <div class="sm:col-span-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Descripción corta</p>
                <p class="text-sm text-gray-700">{{ $product->short_description }}</p>
            </div>
            @endif

            @if($product->description)
            <div class="sm:col-span-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Descripción</p>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $product->description }}</p>
            </div>
            @endif

            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Unidad de medida</p>
                <p class="text-sm text-gray-900 font-medium">{{ \App\Support\Units::abbr($product->unit ?? 'litro') }} ({{ $product->unit ?? 'litro' }})</p>
            </div>

            @if($product->coverage)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Rendimiento</p>
                <p class="text-sm text-gray-900 font-medium">{{ number_format($product->coverage, 2) }} m² / {{ \App\Support\Units::abbr($product->unit ?? 'litro') }}</p>
            </div>
            @endif

            @if(!empty($product->available_colors))
            <div class="sm:col-span-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Colores disponibles</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($product->available_colors as $color)
                    <span class="text-xs font-medium text-gray-600 bg-gray-100 px-2.5 py-1 rounded-full">{{ $color }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Costo y precios --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Costos y precios</h3>
        </div>
        <div class="px-6 py-5 grid sm:grid-cols-3 gap-x-8 gap-y-4">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Costo de compra</p>
                @if($product->costo_compra)
                <p class="text-sm text-gray-900 font-medium">
                    ${{ number_format($product->costo_compra, 2) }}
                    @if($product->unidad_compra)
                    <span class="text-gray-400 font-normal">/ {{ $product->unidad_compra }}</span>
                    @endif
                </p>
                @else
                <p class="text-sm text-gray-400">—</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Margen de ganancia</p>
                @if($product->porcentaje_ganancia)
                <p class="text-sm text-gray-900 font-medium">{{ number_format($product->porcentaje_ganancia, 1) }}%</p>
                @else
                <p class="text-sm text-gray-400">—</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Precio / {{ \App\Support\Units::abbr($product->unit ?? 'litro') }} <span class="font-normal normal-case">(calculado)</span></p>
                @if($product->price)
                <p class="text-sm text-gray-900 font-bold">${{ number_format($product->price, 2) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Costo × (1 + margen%) — base para presentaciones</p>
                @else
                <p class="text-sm text-gray-400">—</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Presentaciones --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Presentaciones y precios de venta</h3>
            <span class="text-xs text-gray-400">{{ $product->presentations->count() }} presentación(es)</span>
        </div>
        @if($product->presentations->isEmpty())
        <div class="px-6 py-8 text-center text-gray-400">
            <p class="text-sm">Sin presentaciones configuradas</p>
            <a href="{{ route('empleados.products.edit', $product) }}" class="text-xs text-brand-700 hover:underline mt-1 inline-block">Agregar desde editar</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nombre</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Contenido</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Precio</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Precio / {{ \App\Support\Units::abbr($product->unit ?? 'litro') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($product->presentations as $pres)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $pres->nombre }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600 tabular-nums">
                            {{ number_format($pres->litros, \App\Support\Units::decimals($product->unit ?? 'litro')) }} {{ \App\Support\Units::abbr($product->unit ?? 'litro') }}
                        </td>
                        <td class="px-6 py-3 text-sm font-bold text-gray-900">${{ number_format($pres->precio, 2) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">
                            @if($pres->litros > 0)
                            ${{ number_format($pres->precio / $pres->litros, 2) }}
                            @else
                            —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Stock por almacén --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Stock por almacén</h3>
        </div>
        @if($product->inventario->isEmpty())
        <div class="px-6 py-8 text-center text-gray-400">
            <p class="text-sm">Sin stock registrado en ningún almacén</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @php $totalStock = 0; @endphp
            @foreach($product->inventario->sortBy('sucursal.nombre') as $inv)
            @php $totalStock += $inv->stock_litros; @endphp
            <div class="px-6 py-3.5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if($inv->sucursal?->foto)
                    <img src="{{ asset('images/sucursales/' . $inv->sucursal->foto) }}"
                         class="w-7 h-7 rounded-lg object-cover border border-gray-100">
                    @else
                    <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $inv->sucursal?->nombre ?? 'Almacén desconocido' }}</p>
                        @if($inv->sucursal?->direccion)
                        <p class="text-xs text-gray-400">{{ $inv->sucursal->direccion }}</p>
                        @endif
                    </div>
                </div>
                <span class="text-sm font-bold tabular-nums whitespace-nowrap {{ $inv->stock_litros > 0 ? 'text-gray-900' : 'text-red-500' }}">
                    {{ number_format($inv->stock_litros, \App\Support\Units::decimals($product->unit ?? 'litro')) }} {{ \App\Support\Units::abbr($product->unit ?? 'litro') }}
                </span>
            </div>
            @endforeach
            <div class="px-6 py-3 flex items-center justify-between gap-4 bg-gray-50/60">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</span>
                <span class="text-sm font-bold tabular-nums text-gray-900">
                    {{ number_format($totalStock, \App\Support\Units::decimals($product->unit ?? 'litro')) }} {{ \App\Support\Units::abbr($product->unit ?? 'litro') }}
                </span>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
