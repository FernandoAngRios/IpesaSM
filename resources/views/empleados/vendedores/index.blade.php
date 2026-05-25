@extends('layouts.empleados')

@section('title', 'Vendedores — IPESA SM')
@section('page-title', 'Vendedores')

@section('content')
<div class="max-w-xl space-y-5">

    @if(session('success'))
    <div class="bg-green-50 border border-green-100 text-green-700 text-sm font-medium px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    {{-- Formulario agregar --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-bold text-gray-800 mb-4">Agregar vendedor</h2>
        <form method="POST" action="{{ route('empleados.vendedores.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="nombre" value="{{ old('nombre') }}"
                   placeholder="Nombre del vendedor"
                   required maxlength="100"
                   class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
            <button type="submit"
                    class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                Agregar
            </button>
        </form>
        @error('nombre')
        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Lista --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if($vendedores->isEmpty())
        <div class="py-14 text-center text-gray-400">
            <p class="font-medium">No hay vendedores registrados</p>
        </div>
        @else
        <ul class="divide-y divide-gray-50">
            @foreach($vendedores as $v)
            <li x-data="{ editing: false }" class="px-5 py-3.5 flex items-center gap-3">

                {{-- Vista normal --}}
                <div x-show="!editing" class="flex items-center gap-3 flex-1 min-w-0">
                    <span class="flex-1 text-sm font-medium text-gray-800 truncate">{{ $v->nombre }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold
                        {{ $v->activo ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                        {{ $v->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                    <button @click="editing = true" type="button"
                            class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-brand-700 hover:bg-brand-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <form method="POST" action="{{ route('empleados.vendedores.destroy', $v) }}"
                          onsubmit="return confirm('¿Eliminar a {{ $v->nombre }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>

                {{-- Vista edición --}}
                <form x-show="editing" method="POST"
                      action="{{ route('empleados.vendedores.update', $v) }}"
                      class="flex items-center gap-2 flex-1">
                    @csrf @method('PUT')
                    <input type="text" name="nombre" value="{{ $v->nombre }}"
                           required maxlength="100"
                           class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                    <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
                        <input type="checkbox" name="activo" value="1" {{ $v->activo ? 'checked' : '' }}
                               class="rounded">
                        Activo
                    </label>
                    <button type="submit"
                            class="text-xs font-semibold text-white bg-brand-700 hover:bg-brand-800 px-3 py-1.5 rounded-lg transition-colors">
                        Guardar
                    </button>
                    <button @click="editing = false" type="button"
                            class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-2 py-1.5 transition-colors">
                        Cancelar
                    </button>
                </form>

            </li>
            @endforeach
        </ul>
        @endif
    </div>

</div>
@endsection
