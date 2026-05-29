@extends('layouts.empleados')

@section('title', 'Editar Producto — IPESA SM')
@section('page-title', 'Editar producto')

@section('content')
<div class="max-w-6xl mx-auto">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <a href="{{ route('empleados.products.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="font-bold text-gray-900">{{ $product->name }}</h2>
            <p class="text-xs text-gray-400">Editar información del producto</p>
        </div>
    </div>

    <form action="{{ route('empleados.products.update', $product) }}" method="POST" enctype="multipart/form-data"
          x-data="{
              costo: '{{ old('costo_compra', $product->costo_compra) }}',
              unidad: '{{ old('unidad_compra', $product->unidad_compra ?? 'litro') }}',
              pct: '{{ old('porcentaje_ganancia', $product->porcentaje_ganancia) }}',
              unidadMedida: '{{ old('unit', $product->unit ?? 'litro') }}',
              unidades: {{ json_encode(\App\Support\Units::all()) }},
              unitAbbrs:    {{ json_encode(\App\Support\Units::abbrs()) }},
              decimalUnits: {{ json_encode(\App\Support\Units::decimalUnits()) }},
              get abbrActual()  { return this.unitAbbrs[this.unidadMedida] ?? this.unidadMedida; },
              get isDecimalUnit() { return this.decimalUnits.includes(this.unidadMedida); },
              get stepActual()  { return this.isDecimalUnit ? 0.001 : 1; },
              conv: {'litro':1,'galón':4,'cubeta':19},
              presentaciones: {{ json_encode($product->presentations->map(fn($p) => ['nombre' => $p->nombre, 'litros' => (string)$p->litros, 'precio' => (string)$p->precio])) }},
              porLitro() {
                  const c = parseFloat(this.costo);
                  const f = this.conv[this.unidad] || 1;
                  return (c > 0 && !isNaN(c)) ? c / f : null;
              },
              precioLitro() {
                  const cpl = this.porLitro();
                  const p = parseFloat(this.pct);
                  return (cpl !== null && !isNaN(p) && p >= 0) ? cpl * (1 + p / 100) : null;
              },
              recalcPresentaciones() {
                  const pl = this.precioLitro();
                  if (pl === null) return;
                  this.presentaciones = this.presentaciones.map(p => ({
                      nombre: p.nombre,
                      litros: p.litros,
                      precio: (parseFloat(p.litros) * pl).toFixed(2)
                  }));
              },
              add()    { this.presentaciones.push({nombre:'', litros:'', precio:''}) },
              remove(i){ this.presentaciones.splice(i, 1) },
              imagePreview: null,
              removeExisting: false,
              onImageChange(e) {
                  const f = e.target.files[0];
                  if (f) { this.imagePreview = URL.createObjectURL(f); this.removeExisting = false; }
              }
          }">
    @csrf @method('PUT')

    {{-- Cuerpo: 2 columnas --}}
    <div class="flex flex-col lg:flex-row lg:divide-x divide-gray-100">

        {{-- Columna izquierda --}}
        <div class="flex-1 min-w-0 divide-y divide-gray-100">

            {{-- Información general --}}
            <div class="px-6 py-5 space-y-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Información general</p>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre del producto *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full border @error('name') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Categoría *</label>
                        <select name="category_id" required
                                class="w-full border @error('category_id') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Unidad de medida *</label>
                        <select name="unit" x-model="unidadMedida" required
                                class="w-full border @error('unit') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                            @foreach(\App\Support\Units::all() as $valor => $etiqueta)
                            <option value="{{ $valor }}" {{ old('unit', $product->unit ?? 'litro') === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                        @error('unit')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Rendimiento (m²/litro)</label>
                        <input type="number" name="coverage" value="{{ old('coverage', $product->coverage) }}" step="0.1" min="0"
                               class="w-full border @error('coverage') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                               placeholder="Solo para pinturas">
                        @error('coverage')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Código de barras</label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <input type="text" name="codigo_barras" value="{{ old('codigo_barras', $product->codigo_barras) }}"
                                   class="w-full border @error('codigo_barras') border-red-400 @else border-gray-200 @enderror rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                                   placeholder="Escanea o escribe el código">
                        </div>
                        @error('codigo_barras')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Costo y margen --}}
            <div class="bg-gray-50/70 px-6 py-5 space-y-4 {{ !auth()->user()->canEditPrices() ? 'opacity-60 pointer-events-none select-none' : '' }}">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Costo y margen</p>
                    @if(!auth()->user()->canEditPrices())
                    <span class="text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">Sin permiso para editar</span>
                    @endif
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Costo de compra <span class="text-gray-400 font-normal" x-show="unidadMedida === 'litro'">(por presentación)</span>
                        </label>
                        <div class="flex gap-2">
                            <div class="relative flex-1 min-w-0">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                                <input type="number" name="costo_compra" x-model="costo" @input="recalcPresentaciones()"
                                       step="0.01" min="0"
                                       class="w-full border @error('costo_compra') border-red-400 @else border-gray-200 @enderror rounded-xl pl-7 pr-2 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors bg-white"
                                       placeholder="0.00">
                            </div>
                            <select name="unidad_compra" x-model="unidad" @change="recalcPresentaciones()"
                                    x-show="unidadMedida === 'litro'"
                                    class="shrink-0 border border-gray-200 rounded-xl px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors bg-white">
                                <option value="litro">Litro</option>
                                <option value="galón">Galón (4 L)</option>
                                <option value="cubeta">Cubeta (19 L)</option>
                            </select>
                        </div>
                        <p class="text-xs text-brand-700 font-medium mt-1.5"
                           x-show="unidadMedida === 'litro' && porLitro() !== null && unidad !== 'litro'">
                            = $<span x-text="porLitro()?.toFixed(2)"></span> / <span x-text="abbrActual"></span>
                        </p>
                        @error('costo_compra')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">% Ganancia</label>
                        <div class="relative">
                            <input type="number" name="porcentaje_ganancia" x-model="pct" @input="recalcPresentaciones()"
                                   step="0.01" min="0"
                                   class="w-full border @error('porcentaje_ganancia') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 pr-10 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors bg-white"
                                   placeholder="0.00">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">%</span>
                        </div>
                        @error('porcentaje_ganancia')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-xl border border-brand-700/20 bg-white px-4 py-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-600">
                        Precio por <span x-text="abbrActual"></span> calculado
                    </span>
                    <span class="text-xl font-black text-brand-700" x-show="precioLitro() !== null">
                        $<span x-text="precioLitro()?.toFixed(2)"></span>
                    </span>
                    <span class="text-sm text-gray-400" x-show="precioLitro() === null">
                        Ingresa costo y % de ganancia
                    </span>
                </div>
                <input type="hidden" name="price" :value="precioLitro() !== null ? precioLitro().toFixed(2) : '{{ $product->price }}'">
            </div>

            {{-- Inventario y descripción --}}
            <div class="px-6 py-5 space-y-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Inventario y descripción</p>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción corta</label>
                    <input type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}" maxlength="300"
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                           placeholder="Resumen breve (máx. 300 caracteres)">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción completa</label>
                    <textarea name="description" rows="4"
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors resize-none">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

        </div>{{-- fin columna izquierda --}}

        {{-- Columna derecha: imagen + presentaciones --}}
        <div class="lg:w-80 xl:w-96 px-6 py-5 flex flex-col gap-4 bg-gray-50/40">

            {{-- Imagen del producto --}}
            <div class="space-y-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Imagen del producto</p>

                @php $hasImage = $product->image && Storage::disk('public')->exists($product->image); @endphp

                {{-- Vista previa de nueva imagen seleccionada --}}
                <div x-show="imagePreview" class="relative rounded-xl overflow-hidden border border-gray-200">
                    <img :src="imagePreview" class="w-full h-40 object-contain bg-white">
                    <button type="button" @click="imagePreview = null; $refs.imgInput.value = ''"
                            class="absolute top-2 right-2 w-7 h-7 bg-white/90 hover:bg-red-50 border border-gray-200 hover:border-red-200 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Imagen actual guardada --}}
                @if($hasImage)
                <div x-show="!imagePreview && !removeExisting" class="relative rounded-xl overflow-hidden border border-gray-200">
                    <img src="{{ Storage::disk('public')->url($product->image) }}" class="w-full h-40 object-contain bg-white">
                    <button type="button" @click="removeExisting = true"
                            class="absolute top-2 right-2 w-7 h-7 bg-white/90 hover:bg-red-50 border border-gray-200 hover:border-red-200 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors shadow-sm"
                            title="Quitar imagen">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <button type="button" @click="$refs.imgInput.click()"
                            class="absolute bottom-2 right-2 text-xs font-medium bg-white/90 hover:bg-white border border-gray-200 px-2.5 py-1 rounded-lg text-gray-500 hover:text-gray-700 transition-colors shadow-sm">
                        Cambiar
                    </button>
                </div>
                @endif

                {{-- Zona de subida (cuando no hay imagen o se marcó quitar) --}}
                <div x-show="{{ $hasImage ? '!imagePreview && removeExisting' : '!imagePreview' }}"
                     class="relative border-2 border-dashed border-gray-200 rounded-xl bg-white flex flex-col items-center justify-center gap-2 py-6 cursor-pointer hover:border-brand-700/40 hover:bg-brand-50/30 transition-colors"
                     @click="$refs.imgInput.click()" @dragover.prevent @drop.prevent="onImageChange({target:{files:$event.dataTransfer.files}})">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-xs text-gray-400 text-center px-2">Haz clic o arrastra una imagen<br><span class="text-gray-300">JPG, PNG, WebP · máx. 3 MB</span></p>
                    @if($hasImage)
                    <button type="button" @click.stop="removeExisting = false"
                            class="text-xs text-brand-700 hover:underline mt-1">Conservar imagen actual</button>
                    @endif
                </div>

                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                       x-ref="imgInput" @change="onImageChange($event)" class="hidden">
                <input type="hidden" name="remove_image" :value="removeExisting && !imagePreview ? '1' : '0'">
                @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="border-t border-gray-100"></div>

            <div class="flex flex-col gap-4 {{ !auth()->user()->canEditPrices() ? 'opacity-60 pointer-events-none select-none' : '' }}">

            <div class="flex items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Presentaciones</p>
                    <p class="text-xs text-gray-400 mt-0.5">Precios al público por tamaño — ajusta si necesitas</p>
                </div>
                @if(!auth()->user()->canEditPrices())
                <span class="shrink-0 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">Sin permiso</span>
                @endif
            </div>

            <div class="grid grid-cols-[1fr_68px_88px_32px] gap-1.5 text-xs font-semibold text-gray-400 uppercase px-1">
                <span>Nombre</span><span>Litros</span><span>Precio</span><span></span>
            </div>

            <div class="space-y-1.5">
                <template x-for="(p, i) in presentaciones" :key="i">
                    <div class="grid grid-cols-[1fr_68px_88px_32px] gap-1.5 items-center">
                        <input type="text" :name="`presentaciones[${i}][nombre]`" x-model="p.nombre"
                               class="border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors bg-white w-full"
                               placeholder="Litro">
                        <input type="number" :name="`presentaciones[${i}][litros]`" x-model="p.litros"
                               :step="stepActual" :min="stepActual"
                               class="border border-gray-200 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors bg-white w-full"
                               placeholder="1">
                        <div class="relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">$</span>
                            <input type="number" :name="`presentaciones[${i}][precio]`" x-model="p.precio"
                                   step="0.01" min="0"
                                   class="border border-gray-200 rounded-lg pl-5 pr-1.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors bg-white w-full"
                                   placeholder="0.00">
                        </div>
                        <button type="button" @click="remove(i)"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <div class="flex flex-col gap-2 pt-1">
                <button type="button" @click="add()"
                        class="flex items-center gap-1.5 text-sm text-brand-700 hover:text-brand-800 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar presentación
                </button>

                <button type="button" @click="recalcPresentaciones()" x-show="precioLitro() !== null"
                        class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-700 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Recalcular precios
                </button>
            </div>

            </div>{{-- fin wrapper presentaciones con permiso --}}

        </div>{{-- fin columna derecha --}}

    </div>{{-- fin flex 2 cols --}}

    {{-- Footer --}}
    <div class="border-t border-gray-100 px-6 py-4 flex flex-wrap items-center gap-6 bg-gray-50/40">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="featured" value="1" {{ old('featured', $product->featured) ? 'checked' : '' }}
                   class="w-4 h-4 rounded accent-brand-700">
            <span class="text-sm font-medium text-gray-700">⭐ Destacado</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="active" value="1" {{ old('active', $product->active) ? 'checked' : '' }}
                   class="w-4 h-4 rounded accent-brand-700">
            <span class="text-sm font-medium text-gray-700">Activo</span>
        </label>
        <div class="ml-auto flex items-center gap-3">
            <a href="{{ route('empleados.products.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 transition-colors">Cancelar</a>
            <button type="submit"
                    class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors">
                Guardar cambios
            </button>
        </div>
    </div>

    </form>
</div>
</div>
@endsection
