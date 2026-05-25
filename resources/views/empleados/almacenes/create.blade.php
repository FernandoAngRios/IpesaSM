@extends('layouts.empleados')

@section('title', 'Nuevo Almacén — IPESA SM')
@section('page-title', 'Nuevo almacén')

@section('content')
<div class="max-w-2xl mx-auto">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <a href="{{ route('empleados.almacenes.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h2 class="font-bold text-gray-900">Crear nuevo almacén</h2>
    </div>

    <form action="{{ route('empleados.almacenes.store') }}" method="POST" enctype="multipart/form-data"
          x-data="{ preview: null }">
        @csrf

        <div class="px-6 py-6 space-y-5">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required
                       class="w-full border @error('nombre') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                       placeholder="Ej: Almacén Central">
                @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dirección *</label>
                <input type="text" name="direccion" value="{{ old('direccion') }}" required
                       class="w-full border @error('direccion') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                       placeholder="Calle, número, colonia, ciudad">
                @error('direccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Teléfono *</label>
                <input type="text" name="telefono" value="{{ old('telefono') }}" required
                       class="w-full border @error('telefono') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                       placeholder="Ej: 555-123-4567">
                @error('telefono')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Foto del almacén</label>
                <div class="space-y-3">
                    <label class="block cursor-pointer">
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-brand-700/40 transition-colors"
                             x-show="!preview">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Haz clic para seleccionar una imagen</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG — máx. 3 MB</p>
                        </div>
                        <div x-show="preview" class="relative rounded-xl overflow-hidden h-48">
                            <img :src="preview" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                <span class="text-white text-sm font-medium">Cambiar foto</span>
                            </div>
                        </div>
                        <input type="file" name="foto" accept="image/*" class="sr-only"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    </label>
                </div>
                @error('foto')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

        </div>

        <div class="border-t border-gray-100 px-6 py-4 flex items-center gap-6 bg-gray-50/40">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded accent-brand-700">
                <span class="text-sm font-medium text-gray-700">Activo</span>
            </label>
            <div class="ml-auto flex items-center gap-3">
                <a href="{{ route('empleados.almacenes.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 transition-colors">Cancelar</a>
                <button type="submit"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors">
                    Crear almacén
                </button>
            </div>
        </div>

    </form>
</div>
</div>
@endsection
