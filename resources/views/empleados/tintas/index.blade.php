@extends('layouts.empleados')

@section('title', 'Tintas — IPESA SM')
@section('page-title', 'Tintas')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">

    {{-- Lista de tintas --}}
    <div class="lg:col-span-2 space-y-4">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
            {{ session('error') }}
        </div>
        @endif

        {{-- Alerta de stock bajo --}}
        @php $bajasStock = $tintas->filter(fn($t) => $t->activa && $t->bajo_stock); @endphp
        @if($bajasStock->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-amber-800">Stock bajo en {{ $bajasStock->count() }} tinta(s)</p>
                <p class="text-xs text-amber-600 mt-0.5">{{ $bajasStock->pluck('nombre')->join(', ') }}</p>
            </div>
        </div>
        @endif

        <p class="text-sm text-gray-500">{{ $tintas->count() }} tintas registradas</p>

        <div class="space-y-3">
            @forelse($tintas as $tinta)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                 x-data="{ movimiento: false, historial: false, editing: false }">

                {{-- Cabecera de la tinta --}}
                <div class="px-6 py-4 flex items-center gap-4">

                    {{-- Muestra de color --}}
                    <div class="w-10 h-10 rounded-xl border border-gray-200 shrink-0 flex items-center justify-center"
                         style="{{ $tinta->color_hex ? 'background-color:' . $tinta->color_hex : 'background-color:#f3f4f6' }}">
                        @unless($tinta->color_hex)
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        @endunless
                    </div>

                    {{-- Nombre + estado --}}
                    <div class="flex-1 min-w-0" x-show="!editing">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-gray-900">{{ $tinta->nombre }}</p>
                            @unless($tinta->activa)
                            <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">Inactiva</span>
                            @endunless
                            @if($tinta->activa && $tinta->bajo_stock)
                            <span class="bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full font-medium">Stock bajo</span>
                            @endif
                        </div>
                        @if($tinta->descripcion)
                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $tinta->descripcion }}</p>
                        @endif
                    </div>

                    {{-- Stock actual --}}
                    <div class="text-right shrink-0" x-show="!editing">
                        <p class="text-xl font-bold {{ $tinta->bajo_stock ? 'text-amber-600' : 'text-gray-900' }}">
                            {{ number_format($tinta->stock_litros, 2) }}
                            <span class="text-sm font-normal text-gray-400">L</span>
                        </p>
                        <p class="text-xs text-gray-400">Mín: {{ number_format($tinta->stock_minimo, 2) }} L</p>
                    </div>

                    {{-- Form edición inline --}}
                    <form action="{{ route('empleados.tintas.update', $tinta) }}" method="POST"
                          class="flex-1 grid grid-cols-2 gap-2" x-show="editing" x-cloak>
                        @csrf @method('PUT')
                        <input type="text" name="nombre" value="{{ $tinta->nombre }}" required placeholder="Nombre"
                               class="col-span-2 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-brand-700">
                        <input type="text" name="descripcion" value="{{ $tinta->descripcion }}" placeholder="Descripción"
                               class="col-span-2 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-brand-700">
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-gray-600 shrink-0">Color:</label>
                            <input type="color" name="color_hex" value="{{ $tinta->color_hex ?? '#cccccc' }}"
                                   class="w-10 h-8 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                        </div>
                        <input type="number" name="stock_minimo" value="{{ $tinta->stock_minimo }}"
                               step="0.001" min="0" placeholder="Mín. L"
                               class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-brand-700">
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input type="checkbox" name="activa" value="1" {{ $tinta->activa ? 'checked' : '' }}
                                   class="accent-brand-700"> Activa
                        </label>
                        <div class="flex gap-2 col-span-2 pt-1">
                            <button type="submit" class="bg-brand-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">Guardar</button>
                            <button type="button" @click="editing = false" class="text-gray-500 text-xs px-3 py-1.5 rounded-lg hover:bg-gray-100">Cancelar</button>
                        </div>
                    </form>
                </div>

                {{-- Botones de acción --}}
                <div class="px-6 pb-4 flex items-center gap-3 flex-wrap" x-show="!editing">
                    <button @click="movimiento = !movimiento; historial = false"
                            class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors"
                            :class="movimiento ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-700 hover:bg-brand-100'">
                        Registrar movimiento
                    </button>
                    <button @click="historial = !historial; movimiento = false"
                            class="text-xs font-medium text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        Historial ({{ $tinta->movimientos->count() }})
                    </button>
                    <a href="{{ route('empleados.tintas.show', $tinta) }}"
                       class="text-xs font-medium text-brand-700 hover:text-brand-800 px-3 py-1.5 rounded-lg hover:bg-brand-50 transition-colors">
                        Ver detalle →
                    </a>
                    @if(auth()->user()->isAdmin())
                    <button @click="editing = true; movimiento = false; historial = false"
                            class="text-xs font-medium text-gray-500 hover:text-brand-700 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        Editar
                    </button>
                    @if($tinta->movimientos->isEmpty())
                    <form action="{{ route('empleados.tintas.destroy', $tinta) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar esta tinta?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                            Eliminar
                        </button>
                    </form>
                    @endif
                    @endif
                </div>

                {{-- Panel: Registrar movimiento --}}
                <div x-show="movimiento" x-cloak
                     class="border-t border-gray-100 bg-gray-50 px-6 py-4">
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">Registrar movimiento</p>
                    <form action="{{ route('empleados.tintas.movimiento', $tinta) }}" method="POST"
                          class="grid sm:grid-cols-4 gap-3">
                        @csrf

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                            <select name="tipo" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:border-brand-700">
                                <option value="entrada">Entrada</option>
                                <option value="uso">Uso en máquina</option>
                                <option value="ajuste">Ajuste (conteo físico)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Cantidad (litros)</label>
                            <input type="number" name="cantidad_litros" step="0.001" min="0.001" required
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-700"
                                   placeholder="0.000">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Referencia (opcional)</label>
                            <div class="flex gap-2">
                                <input type="text" name="referencia"
                                       class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-700"
                                       placeholder="Nota o referencia">
                                <button type="submit"
                                        class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors shrink-0">
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </form>
                    <p class="text-xs text-gray-400 mt-2">
                        <strong>Ajuste:</strong> establece el stock al valor exacto (conteo físico).
                        <strong>Entrada:</strong> suma al stock.
                        <strong>Uso:</strong> resta al stock.
                    </p>
                </div>

                {{-- Panel: Historial --}}
                <div x-show="historial" x-cloak
                     class="border-t border-gray-100">
                    @if($tinta->movimientos->isEmpty())
                    <p class="text-center text-sm text-gray-400 py-6">Sin movimientos registrados.</p>
                    @else
                    <div class="divide-y divide-gray-50">
                        @foreach($tinta->movimientos as $mov)
                        <div class="flex items-center gap-4 px-6 py-3">
                            <span @class([
                                'text-xs font-semibold px-2.5 py-0.5 rounded-full shrink-0',
                                'bg-emerald-100 text-emerald-700' => $mov->tipo === 'entrada',
                                'bg-red-100 text-red-700'         => $mov->tipo === 'uso',
                                'bg-blue-100 text-blue-700'       => $mov->tipo === 'ajuste',
                            ])>
                                {{ ucfirst($mov->tipo) }}
                            </span>
                            <span class="font-semibold text-sm text-gray-900 shrink-0">
                                {{ $mov->tipo === 'uso' ? '-' : ($mov->tipo === 'ajuste' ? '=' : '+') }}{{ number_format($mov->cantidad_litros, 3) }} L
                            </span>
                            <span class="text-sm text-gray-500 flex-1 truncate">{{ $mov->referencia ?: '—' }}</span>
                            <span class="text-xs text-gray-400 shrink-0">{{ $mov->usuario->name }}</span>
                            <span class="text-xs text-gray-400 shrink-0">{{ $mov->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endforeach
                    </div>
                    @if($tinta->movimientos->count() >= 10)
                    <p class="text-xs text-center text-gray-400 py-2">Mostrando últimos 10 movimientos</p>
                    @endif
                    @endif
                </div>

            </div>
            @empty
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                <p>No hay tintas registradas. Agrega la primera.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Panel lateral: Nueva tinta (solo admin) --}}
    @if(auth()->user()->isAdmin())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden self-start sticky top-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-900">Nueva tinta</h2>
        </div>
        <form action="{{ route('empleados.tintas.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required
                       class="w-full border @error('nombre') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                       placeholder="Ej: Rojo Carmín">
                @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion') }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                       placeholder="Notas opcionales">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Color de muestra</label>
                    <input type="color" name="color_hex" value="{{ old('color_hex', '#cccccc') }}"
                           class="w-full h-10 rounded-xl border border-gray-200 cursor-pointer p-1">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Stock mínimo (L)</label>
                    <input type="number" name="stock_minimo" value="{{ old('stock_minimo', 0) }}"
                           step="0.001" min="0"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold py-2.5 rounded-xl transition-colors">
                Crear tinta
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
