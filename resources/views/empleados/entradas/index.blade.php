@extends('layouts.empleados')

@section('title', 'Entradas de material — IPESA SM')
@section('page-title', 'Entradas de material')

@section('header-actions')
<a href="{{ route('empleados.entradas.create') }}"
   class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Registrar entrada
</a>
@endsection

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between gap-4 flex-wrap">
        <p class="text-sm text-gray-500">{{ $entradas->total() }} entradas registradas</p>
        @if($sucursales->count() > 1)
        <form method="GET" action="{{ route('empleados.entradas.index') }}" class="flex items-center gap-3">
            <select name="sucursal_id" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                <option value="">@if(auth()->user()->isAdmin()) Todos los almacenes @else Mis almacenes @endif</option>
                @foreach($sucursales as $s)
                <option value="{{ $s->id }}" @selected(request('sucursal_id') == $s->id)>{{ $s->nombre }}</option>
                @endforeach
            </select>
            @if(request('sucursal_id'))
            <a href="{{ route('empleados.entradas.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">Limpiar</a>
            @endif
        </form>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if($entradas->isEmpty())
        <div class="py-20 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="font-medium">Sin entradas registradas</p>
            <a href="{{ route('empleados.entradas.create') }}" class="mt-4 inline-block text-sm text-brand-700 hover:underline">Registrar la primera</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fecha</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Proveedor</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Producto</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Almacén</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Cantidad</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Registró</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($entradas as $entrada)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-3 text-xs text-gray-500 whitespace-nowrap">
                            {{ $entrada->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-sm font-semibold text-gray-900">{{ $entrada->proveedor_nombre }}</span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-700">{{ $entrada->product->name }}</td>
                        <td class="px-6 py-3">
                            <a href="{{ route('empleados.almacenes.show', $entrada->sucursal_id) }}"
                               class="text-xs font-medium text-gray-600 hover:text-brand-700 transition-colors">
                                {{ $entrada->sucursal->nombre }}
                            </a>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <span class="font-bold text-sm text-emerald-600 whitespace-nowrap">
                                +{{ number_format($entrada->cantidad_litros, \App\Support\Units::decimals($entrada->product->unit ?? 'litro')) }}
                                {{ \App\Support\Units::abbr($entrada->product->unit ?? 'litro') }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-500">{{ $entrada->user->name }}</td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('empleados.entradas.show', $entrada) }}"
                               class="text-xs font-semibold text-brand-700 hover:text-brand-800 px-2.5 py-1 rounded-lg hover:bg-brand-50 transition-colors">
                                Ver
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($entradas->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $entradas->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
