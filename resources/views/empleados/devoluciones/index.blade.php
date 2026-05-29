@extends('layouts.empleados')

@section('title', 'Devoluciones — IPESA SM')
@section('page-title', 'Devoluciones')

@section('content')
<div class="space-y-5">

    @if($devoluciones->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center py-20 text-center">
        <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
        </svg>
        <p class="font-semibold text-gray-500">Sin devoluciones registradas</p>
        <p class="text-sm text-gray-400 mt-1">Las devoluciones aparecerán aquí una vez que se registren desde una venta.</p>
    </div>

    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider"># Dev.</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Venta</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Almacén</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Registró</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Motivo</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Fecha</th>
                        <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Total</th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($devoluciones as $dev)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4 font-semibold text-gray-900 tabular-nums">
                            #{{ str_pad($dev->id, 6, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-5 py-4">
                            <a href="{{ route('empleados.ventas.show', $dev->venta) }}"
                               class="font-medium text-brand-700 hover:text-brand-800 transition-colors">
                                #{{ str_pad($dev->venta_id, 6, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td class="px-5 py-4 text-gray-600 hidden sm:table-cell">
                            {{ $dev->sucursal->nombre }}
                        </td>
                        <td class="px-5 py-4 text-gray-500 hidden md:table-cell">
                            {{ $dev->user->name }}
                        </td>
                        <td class="px-5 py-4 text-gray-400 hidden lg:table-cell max-w-[200px]">
                            <span class="truncate block">{{ $dev->motivo ?: '—' }}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-400 whitespace-nowrap">
                            {{ $dev->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-4 text-right font-bold text-red-600 tabular-nums whitespace-nowrap">
                            − ${{ number_format($dev->total_devuelto, 2) }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('empleados.devoluciones.show', $dev) }}"
                               class="text-xs font-semibold text-brand-700 hover:text-brand-800 transition-colors">
                                Ver →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($devoluciones->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $devoluciones->links() }}
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
