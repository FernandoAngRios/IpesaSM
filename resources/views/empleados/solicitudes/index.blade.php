@extends('layouts.empleados')
@section('title', 'Solicitudes de Mercancía')
@section('page-title', 'Solicitudes de Mercancía')

@section('content')
<div class="space-y-4">

    {{-- Cabecera --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex gap-1 bg-gray-100 p-1 rounded-xl text-sm">
            @foreach(['todas' => 'Todas', 'pendiente' => 'Pendientes', 'enviada' => 'Enviadas', 'recibida' => 'Recibidas', 'cancelada' => 'Canceladas'] as $val => $label)
            <a href="{{ route('empleados.solicitudes.index', ['estado' => $val === 'todas' ? null : $val]) }}"
               class="px-3 py-1.5 rounded-lg font-medium transition-colors
                      {{ $estado === $val ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
        <a href="{{ route('empleados.solicitudes.create') }}"
           class="btn-primary flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva solicitud
        </a>
    </div>

    {{-- Lista --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if($solicitudes->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-sm font-medium">Sin solicitudes</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50/50">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">#</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Solicita</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Pide a</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Productos</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Estado</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($solicitudes as $s)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3.5 font-mono text-xs text-gray-400">#{{ str_pad($s->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-5 py-3.5 font-medium text-gray-800">{{ $s->solicitanteSucursal->nombre }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $s->origenSucursal->nombre }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $s->items->count() }} producto(s)</td>
                    <td class="px-5 py-3.5">
                        @php
                            $badges = [
                                'pendiente' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                'enviada'   => 'bg-blue-50 text-blue-700 ring-blue-200',
                                'recibida'  => 'bg-green-50 text-green-700 ring-green-200',
                                'cancelada' => 'bg-gray-100 text-gray-400 ring-gray-200',
                            ];
                            $labels = [
                                'pendiente' => 'Pendiente',
                                'enviada'   => 'Enviada',
                                'recibida'  => 'Recibida',
                                'cancelada' => 'Cancelada',
                            ];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $badges[$s->estado] ?? '' }}">
                            {{ $labels[$s->estado] ?? $s->estado }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $s->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3.5">
                        <a href="{{ route('empleados.solicitudes.show', $s) }}"
                           class="text-brand-600 hover:text-brand-800 font-medium text-xs transition-colors">
                            Ver →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($solicitudes->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $solicitudes->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
