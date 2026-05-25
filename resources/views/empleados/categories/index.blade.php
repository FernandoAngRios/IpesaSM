@extends('layouts.empleados')

@section('title', 'Categorías — IPESA SM')
@section('page-title', 'Categorías')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">

    {{-- Lista de categorías --}}
    <div class="lg:col-span-2 space-y-4">
        <p class="text-sm text-gray-500">{{ $categories->count() }} categorías registradas</p>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            @if($categories->isEmpty())
            <div class="py-16 text-center text-gray-400">
                {!! App\Models\Category::svgForKey('paint_special', 48, 'mx-auto mb-2 opacity-30') !!}
                <p>No hay categorías. Crea la primera.</p>
            </div>
            @else
            <div class="divide-y divide-gray-50">
                @foreach($categories as $cat)
                <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50/50 transition-colors"
                     x-data="{ editing: false }">

                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0"
                         style="background-color: {{ $cat->color }}">
                        {!! $cat->iconSvg(20) !!}
                    </div>

                    <div class="flex-1 min-w-0" x-show="!editing">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-sm text-gray-900">{{ $cat->name }}</p>
                            @if(! $cat->active)
                            <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">Inactiva</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 truncate">{{ $cat->description ?: 'Sin descripción' }}</p>
                    </div>

                    <div class="flex items-center gap-3 text-sm shrink-0" x-show="!editing">
                        <span class="text-gray-400">{{ $cat->products_count }} productos</span>
                        @if(auth()->user()->isAdmin())
                        <button @click="editing = true"
                                class="text-brand-700 hover:text-brand-800 font-semibold text-xs px-2.5 py-1 rounded-lg hover:bg-brand-50 transition-colors">
                            Editar
                        </button>
                        @if($cat->products_count === 0)
                        <form action="{{ route('empleados.categories.destroy', $cat) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar esta categoría?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-red-600 hover:text-red-700 font-semibold text-xs px-2.5 py-1 rounded-lg hover:bg-red-50 transition-colors">
                                Eliminar
                            </button>
                        </form>
                        @endif
                        @endif
                    </div>

                    {{-- Formulario de edición inline --}}
                    <form action="{{ route('empleados.categories.update', $cat) }}" method="POST"
                          class="flex-1" x-show="editing" x-cloak>
                        @csrf @method('PUT')

                        <div class="grid grid-cols-2 gap-2 mb-2">
                            <input type="text" name="name" value="{{ $cat->name }}" required
                                   class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-brand-700">
                            <div class="flex gap-2">
                                <input type="color" name="color" value="{{ $cat->color }}"
                                       class="w-10 h-9 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                                <input type="number" name="order" value="{{ $cat->order }}" min="0"
                                       class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-brand-700" placeholder="Orden">
                            </div>
                        </div>

                        <input type="text" name="description" value="{{ $cat->description }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-brand-700 mb-3"
                               placeholder="Descripción">

                        {{-- Icon picker compacto --}}
                        <div data-icon-picker class="mb-3">
                            <p class="text-xs font-semibold text-gray-500 mb-1.5">Icono</p>
                            <input type="hidden" name="icon" value="{{ $cat->icon }}">
                            <div class="grid grid-cols-8 gap-1.5">
                                @foreach($iconOptions as $key => $label)
                                <button type="button"
                                        data-icon="{{ $key }}"
                                        data-label="{{ $label }}"
                                        onclick="pickIcon(this)"
                                        title="{{ $label }}"
                                        class="h-8 w-8 rounded-lg flex items-center justify-center transition-all duration-150 {{ $cat->icon === $key ? 'bg-brand-700 text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                    {!! App\Models\Category::svgForKey($key, 16) !!}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <label class="flex items-center gap-1.5 text-xs mb-3">
                            <input type="checkbox" name="active" value="1" {{ $cat->active ? 'checked' : '' }} class="accent-brand-700">
                            Activa
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-brand-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">Guardar</button>
                            <button type="button" @click="editing = false" class="text-gray-500 text-xs px-3 py-1.5 rounded-lg hover:bg-gray-100">Cancelar</button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Formulario nueva categoría (solo admin) --}}
    @if(auth()->user()->isAdmin())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden self-start">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-900">Nueva categoría</h2>
        </div>
        <form action="{{ route('empleados.categories.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border @error('name') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                       placeholder="Ej: Pinturas Vinílicas">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción</label>
                <textarea name="description" rows="2"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors resize-none"
                          placeholder="Breve descripción...">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Color</label>
                    <input type="color" name="color" value="{{ old('color', '#1a3c5e') }}"
                           class="w-full h-10 rounded-xl border border-gray-200 cursor-pointer p-1">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Orden</label>
                    <input type="number" name="order" value="{{ old('order', 0) }}" min="0"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                </div>
            </div>

            {{-- Icon picker --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Icono</label>

                <div data-icon-picker>
                    <input type="hidden" name="icon" value="{{ old('icon', 'paint_interior') }}">

                    {{-- Preview del seleccionado --}}
                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 mb-3">
                        <div class="w-9 h-9 rounded-xl bg-brand-700/10 flex items-center justify-center text-brand-700 shrink-0">
                            @foreach($iconOptions as $key => $label)
                            <span data-icon-preview-svg="{{ $key }}" {{ old('icon', 'paint_interior') !== $key ? 'hidden' : '' }}>
                                {!! App\Models\Category::svgForKey($key, 20) !!}
                            </span>
                            @endforeach
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide leading-none mb-0.5">Seleccionado</p>
                            <p class="text-sm font-semibold text-gray-800 leading-snug" data-icon-preview-label>
                                {{ $iconOptions[old('icon', 'paint_interior')] }}
                            </p>
                        </div>
                    </div>

                    {{-- Grid de opciones --}}
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($iconOptions as $key => $label)
                        <button type="button"
                                data-icon="{{ $key }}"
                                data-label="{{ $label }}"
                                onclick="pickIcon(this)"
                                title="{{ $label }}"
                                class="h-11 rounded-xl flex items-center justify-center transition-all duration-150 {{ old('icon', 'paint_interior') === $key ? 'bg-brand-700 text-white shadow-md shadow-brand-700/25 scale-105' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700' }}">
                            {!! App\Models\Category::svgForKey($key, 20) !!}
                        </button>
                        @endforeach
                    </div>
                </div>

                @error('icon')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded accent-brand-700">
                <span class="text-sm font-medium text-gray-700">Activa</span>
            </label>

            <button type="submit"
                    class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold py-2.5 rounded-xl transition-colors">
                Crear categoría
            </button>
        </form>
    </div>
    @endif
</div>

<script>
function pickIcon(btn) {
    var picker = btn.closest('[data-icon-picker]');
    var key    = btn.dataset.icon;

    picker.querySelector('[name="icon"]').value = key;

    var previewSvgs = picker.querySelectorAll('[data-icon-preview-svg]');
    previewSvgs.forEach(function(el) {
        el.hidden = el.dataset.iconPreviewSvg !== key;
    });

    var previewLabel = picker.querySelector('[data-icon-preview-label]');
    if (previewLabel) previewLabel.textContent = btn.dataset.label;

    picker.querySelectorAll('[data-icon]').forEach(function(b) {
        var selected = b === btn;
        b.classList.toggle('bg-brand-700', selected);
        b.classList.toggle('text-white',   selected);
        b.classList.toggle('shadow-md',    selected);
        b.classList.toggle('bg-gray-100',  !selected);
        b.classList.toggle('text-gray-500',!selected);
    });
}
</script>
@endsection
