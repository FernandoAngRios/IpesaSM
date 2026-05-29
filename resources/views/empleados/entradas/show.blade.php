@extends('layouts.empleados')

@section('title', 'Entrada #' . $entrada->id . ' — IPESA SM')
@section('page-title', 'Detalle de entrada')

@section('content')
<div class="max-w-xl mx-auto">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <a href="{{ route('empleados.entradas.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="font-bold text-gray-900">Entrada #{{ $entrada->id }}</h2>
            <p class="text-xs text-gray-400">{{ $entrada->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="px-6 py-6 space-y-5">

        {{-- Proveedor --}}
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
            <div class="w-9 h-9 bg-emerald-600 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Proveedor</p>
                <p class="font-bold text-gray-900">{{ $entrada->proveedor_nombre }}</p>
            </div>
        </div>

        {{-- Producto y cantidad --}}
        <div class="flex items-center gap-4 bg-brand-50 rounded-xl px-4 py-4">
            <div class="w-10 h-10 bg-brand-700 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900">{{ $entrada->product->name }}</p>
                <p class="text-xs text-gray-500">{{ $entrada->product->category->name }}</p>
            </div>
            @php $eUnit = $entrada->product->unit ?? 'litro'; @endphp
            <div class="text-right shrink-0">
                <p class="text-2xl font-black text-emerald-600">+{{ number_format($entrada->cantidad_litros, \App\Support\Units::decimals($eUnit)) }}</p>
                <p class="text-xs text-emerald-700 font-medium">{{ \App\Support\Units::abbr($eUnit) }}</p>
            </div>
        </div>

        {{-- Almacén destino --}}
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-400">Almacén que recibió</p>
                <a href="{{ route('empleados.almacenes.show', $entrada->sucursal_id) }}"
                   class="font-bold text-gray-900 hover:text-brand-700 transition-colors">
                    {{ $entrada->sucursal->nombre }}
                </a>
            </div>
        </div>

        {{-- Meta --}}
        <div class="border border-gray-100 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50/50">
                <span class="text-xs text-gray-500">Registrado por</span>
                <span class="text-xs font-semibold text-gray-900">{{ $entrada->user->name }}</span>
            </div>
            <div class="flex items-center justify-between px-4 py-2.5 border-t border-gray-100">
                <span class="text-xs text-gray-500">Fecha</span>
                <span class="text-xs font-semibold text-gray-900">{{ $entrada->created_at->format('d/m/Y \a \l\a\s H:i') }}</span>
            </div>
            @if($entrada->nota)
            <div class="px-4 py-2.5 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-1">Nota</p>
                <p class="text-sm text-gray-900">{{ $entrada->nota }}</p>
            </div>
            @endif
        </div>

    </div>

    @if(auth()->user()->isAdmin())
    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3 bg-gray-50/40">
        <a href="{{ route('empleados.entradas.edit', $entrada) }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-brand-700 hover:text-brand-800 border border-brand-200 hover:border-brand-300 bg-brand-50 hover:bg-brand-100 px-4 py-2 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
        </a>
        <form action="{{ route('empleados.entradas.destroy', $entrada) }}" method="POST"
              onsubmit="return confirm('¿Eliminar esta entrada? El stock se revertirá automáticamente.')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-red-600 hover:text-red-700 border border-red-200 hover:border-red-300 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Eliminar
            </button>
        </form>
    </div>
    @endif

</div>
</div>
@endsection
