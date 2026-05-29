@extends('layouts.empleados')

@section('title', 'Almacenes — IPESA SM')
@section('page-title', 'Almacenes')

@section('header-actions')
<a href="{{ route('empleados.exportar.inventario') }}"
   class="inline-flex items-center gap-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    Exportar stock
</a>
@if(auth()->user()->isAdmin())
<a href="{{ route('empleados.almacenes.create') }}"
   class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo almacén
</a>
@endif
@endsection

@section('content')
<div class="space-y-6">

    <p class="text-sm text-gray-500">{{ $sucursales->count() }} almacenes registrados</p>

    @if($sucursales->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-20 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <p class="font-medium">No hay almacenes aún</p>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('empleados.almacenes.create') }}" class="mt-4 inline-block text-sm text-brand-700 hover:underline">Crear el primero</a>
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($sucursales as $sucursal)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">

            {{-- Foto --}}
            <div class="h-40 bg-gray-100 relative overflow-hidden">
                @if($sucursal->foto)
                <img src="{{ asset('images/sucursales/' . $sucursal->foto) }}"
                     alt="{{ $sucursal->nombre }}"
                     class="w-full h-full object-cover">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-14 h-14 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                @endif

                <div class="absolute top-3 right-3">
                    @if($sucursal->activo)
                    <span class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm">Activo</span>
                    @else
                    <span class="bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm">Inactivo</span>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="px-5 py-4 flex-1 flex flex-col gap-3">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">{{ $sucursal->nombre }}</h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $sucursal->direccion }}</p>
                </div>

                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    {{ $sucursal->telefono }}
                </div>

                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    {{ $sucursal->inventario_count }} {{ Str::plural('producto', $sucursal->inventario_count) }} en inventario
                </div>
            </div>

            {{-- Acciones --}}
            <div class="px-5 py-3 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('empleados.almacenes.show', $sucursal) }}"
                   class="flex-1 text-center text-sm font-semibold text-brand-700 hover:text-brand-800 px-3 py-1.5 rounded-lg hover:bg-brand-50 transition-colors">
                    Ver inventario
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('empleados.almacenes.edit', $sucursal) }}"
                   class="flex-1 text-center text-sm font-semibold text-gray-600 hover:text-gray-800 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    Editar
                </a>
                <form action="{{ route('empleados.almacenes.destroy', $sucursal) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar {{ $sucursal->nombre }}? Se eliminará todo su inventario.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-sm font-semibold text-red-500 hover:text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                        Eliminar
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
