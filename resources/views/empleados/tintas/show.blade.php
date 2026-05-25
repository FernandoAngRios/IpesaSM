@extends('layouts.empleados')

@section('title', $tinta->nombre . ' — Tintas — IPESA SM')
@section('page-title', 'Detalle de tinta')

@section('content')
<div class="max-w-2xl mx-auto space-y-5"
     x-data="{ movimiento: false, editing: false }">

    {{-- Volver --}}
    <a href="{{ route('empleados.tintas.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Tintas
    </a>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    {{-- Cabecera --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
        <div class="flex items-start gap-4">

            {{-- Muestra de color --}}
            <div class="w-14 h-14 rounded-xl border border-gray-200 shrink-0 flex items-center justify-center"
                 style="{{ $tinta->color_hex ? 'background-color:' . $tinta->color_hex : 'background-color:#f3f4f6' }}">
                @unless($tinta->color_hex)
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                @endunless
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl font-black text-gray-900">{{ $tinta->nombre }}</h2>
                    @unless($tinta->activa)
                    <span class="bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-0.5 rounded-full">Inactiva</span>
                    @endunless
                    @if($tinta->activa && $tinta->bajo_stock)
                    <span class="bg-amber-100 text-amber-700 text-xs font-semibold px-2.5 py-0.5 rounded-full">Stock bajo</span>
                    @endif
                </div>
                @if($tinta->descripcion)
                <p class="text-sm text-gray-500 mt-1">{{ $tinta->descripcion }}</p>
                @endif
            </div>

            {{-- Stock --}}
            <div class="text-right shrink-0">
                <p class="text-3xl font-black {{ $tinta->bajo_stock ? 'text-amber-600' : 'text-gray-900' }}">
                    {{ number_format($tinta->stock_litros, 2) }}
                    <span class="text-base font-normal text-gray-400">L</span>
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Mín: {{ number_format($tinta->stock_minimo, 2) }} L</p>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="mt-5 flex items-center gap-3 flex-wrap" x-show="!editing">
            <button @click="movimiento = !movimiento"
                    class="text-sm font-semibold px-4 py-2 rounded-xl transition-colors"
                    :class="movimiento ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-700 hover:bg-brand-100'">
                Registrar movimiento
            </button>
            @if(auth()->user()->isAdmin())
            <button @click="editing = true; movimiento = false"
                    class="text-sm font-medium text-gray-500 hover:text-brand-700 px-4 py-2 rounded-xl hover:bg-gray-100 transition-colors">
                Editar
            </button>
            @if($movimientos->total() === 0)
            <form action="{{ route('empleados.tintas.destroy', $tinta) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar esta tinta?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="text-sm font-medium text-red-500 hover:text-red-700 px-4 py-2 rounded-xl hover:bg-red-50 transition-colors">
                    Eliminar
                </button>
            </form>
            @endif
            @endif
        </div>

        {{-- Form: Registrar movimiento --}}
        <div x-show="movimiento" x-cloak
             class="mt-5 border-t border-gray-100 pt-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Registrar movimiento</p>
            <form action="{{ route('empleados.tintas.movimiento', $tinta) }}" method="POST"
                  class="grid sm:grid-cols-4 gap-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                    <select name="tipo" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:border-brand-700">
                        <option value="entrada">Entrada</option>
                        <option value="uso">Uso en máquina</option>
                        <option value="ajuste">Ajuste (conteo físico)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cantidad (litros)</label>
                    <input type="number" name="cantidad_litros" step="0.001" min="0.001" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-700"
                           placeholder="0.000">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Referencia (opcional)</label>
                    <div class="flex gap-2">
                        <input type="text" name="referencia"
                               class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-700"
                               placeholder="Nota o referencia">
                        <button type="submit"
                                class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors shrink-0">
                            Guardar
                        </button>
                    </div>
                </div>
            </form>
            <p class="text-xs text-gray-400 mt-2">
                <strong>Entrada:</strong> suma al stock.
                <strong>Uso:</strong> resta al stock.
                <strong>Ajuste:</strong> establece el stock al valor exacto.
            </p>
        </div>

        {{-- Form: Editar (admin) --}}
        @if(auth()->user()->isAdmin())
        <div x-show="editing" x-cloak class="mt-5 border-t border-gray-100 pt-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Editar tinta</p>
            <form action="{{ route('empleados.tintas.update', $tinta) }}" method="POST"
                  class="grid sm:grid-cols-2 gap-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre *</label>
                    <input type="text" name="nombre" value="{{ $tinta->nombre }}" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-700">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                    <input type="text" name="descripcion" value="{{ $tinta->descripcion }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-700">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Color de muestra</label>
                    <input type="color" name="color_hex" value="{{ $tinta->color_hex ?? '#cccccc' }}"
                           class="w-full h-10 rounded-xl border border-gray-200 cursor-pointer p-1">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Stock mínimo (L)</label>
                    <input type="number" name="stock_minimo" value="{{ $tinta->stock_minimo }}"
                           step="0.001" min="0"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-brand-700">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="activa" value="1" id="activa_{{ $tinta->id }}"
                           {{ $tinta->activa ? 'checked' : '' }} class="accent-brand-700">
                    <label for="activa_{{ $tinta->id }}" class="text-sm text-gray-700">Activa</label>
                </div>
                <div class="sm:col-span-2 flex gap-3">
                    <button type="submit"
                            class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors">
                        Guardar cambios
                    </button>
                    <button type="button" @click="editing = false"
                            class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-100 transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
        @endif

    </div>

    {{-- Historial de movimientos --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Historial de movimientos</h3>
            <span class="text-xs text-gray-400">{{ $movimientos->total() }} en total</span>
        </div>

        @if($movimientos->isEmpty())
        <div class="py-12 text-center">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-sm text-gray-400">Sin movimientos registrados.</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($movimientos as $mov)
            <div class="flex items-center gap-4 px-6 py-3.5">
                <span @class([
                    'text-xs font-semibold px-2.5 py-0.5 rounded-full shrink-0 whitespace-nowrap',
                    'bg-emerald-100 text-emerald-700' => $mov->tipo === 'entrada',
                    'bg-red-100 text-red-700'         => $mov->tipo === 'uso',
                    'bg-blue-100 text-blue-700'        => $mov->tipo === 'ajuste',
                ])>
                    {{ ucfirst($mov->tipo) }}
                </span>
                <span class="font-semibold text-sm text-gray-900 shrink-0 tabular-nums">
                    @if($mov->tipo === 'uso') −
                    @elseif($mov->tipo === 'ajuste') =
                    @else +
                    @endif
                    {{ number_format($mov->cantidad_litros, 3) }} L
                </span>
                <span class="text-sm text-gray-500 flex-1 truncate">{{ $mov->referencia ?: '—' }}</span>
                <span class="text-xs text-gray-400 shrink-0 hidden sm:block">{{ $mov->usuario->name }}</span>
                <span class="text-xs text-gray-400 shrink-0 whitespace-nowrap">{{ $mov->created_at->format('d/m/Y H:i') }}</span>
            </div>
            @endforeach
        </div>

        @if($movimientos->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $movimientos->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
