@extends('layouts.empleados')
@section('title', 'Solicitud #' . str_pad($solicitud->id, 5, '0', STR_PAD_LEFT))
@section('page-title', 'Solicitud de Mercancía')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Cabecera --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('empleados.solicitudes.index') }}"
               class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-gray-900">Solicitud #{{ str_pad($solicitud->id, 5, '0', STR_PAD_LEFT) }}</h2>
                <p class="text-xs text-gray-400 mt-0.5">Creada {{ $solicitud->created_at->format('d/m/Y H:i') }} por {{ $solicitud->user->name }}</p>
            </div>
        </div>
        @php
            $badges = ['pendiente'=>'bg-amber-50 text-amber-700 ring-amber-200','enviada'=>'bg-blue-50 text-blue-700 ring-blue-200','recibida'=>'bg-green-50 text-green-700 ring-green-200','cancelada'=>'bg-gray-100 text-gray-400 ring-gray-200'];
            $labels = ['pendiente'=>'Pendiente','enviada'=>'Enviada','recibida'=>'Recibida','cancelada'=>'Cancelada'];
        @endphp
        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold ring-1 {{ $badges[$solicitud->estado] ?? '' }}">
            {{ $labels[$solicitud->estado] ?? $solicitud->estado }}
        </span>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-100 rounded-xl px-4 py-3 text-sm text-green-700 font-medium">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-sm text-red-600">
        {{ session('error') }}
    </div>
    @endif

    {{-- Info general --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 grid grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Solicita</p>
            <p class="font-semibold text-gray-800">{{ $solicitud->solicitanteSucursal->nombre }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Pide a</p>
            <p class="font-semibold text-gray-800">{{ $solicitud->origenSucursal->nombre }}</p>
        </div>
        @if($solicitud->notas_solicitud)
        <div class="col-span-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Notas de solicitud</p>
            <p class="text-gray-600">{{ $solicitud->notas_solicitud }}</p>
        </div>
        @endif
        @if($solicitud->notas_envio)
        <div class="col-span-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Notas de envío</p>
            <p class="text-gray-600">{{ $solicitud->notas_envio }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Procesado por {{ $solicitud->procesadoPor?->name }} el {{ $solicitud->procesado_at?->format('d/m/Y H:i') }}</p>
        </div>
        @endif
        @if($solicitud->notas_recepcion)
        <div class="col-span-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Notas de recepción</p>
            <p class="text-gray-600">{{ $solicitud->notas_recepcion }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Confirmado por {{ $solicitud->recibidoPor?->name }} el {{ $solicitud->recibido_at?->format('d/m/Y H:i') }}</p>
        </div>
        @endif
    </div>

    {{-- Tabla de productos --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Productos</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50/50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Producto</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Pedido</th>
                    @if(!$solicitud->esPendiente())
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Enviado</th>
                    @endif
                    @if($solicitud->esRecibida())
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Recibido</th>
                    @endif
                    @if(!$solicitud->esPendiente() && !$solicitud->esRecibida())
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Disp. en origen</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($solicitud->items as $item)
                <tr>
                    <td class="px-5 py-3.5">
                        <p class="font-medium text-gray-800">{{ $item->product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $item->product->category->name }}</p>
                    </td>
                    @php
                        $unit     = $item->product->unit;
                        $dec      = \App\Support\Units::decimals($unit);
                        $abbr     = \App\Support\Units::abbr($unit);
                    @endphp
                    <td class="px-4 py-3.5 text-right tabular-nums text-gray-700">
                        {{ number_format($item->cantidad_solicitada, $dec) }} {{ $abbr }}
                    </td>
                    @if(!$solicitud->esPendiente())
                    <td class="px-4 py-3.5 text-right tabular-nums">
                        @if($item->cantidad_enviada !== null)
                            <span class="{{ $item->cantidad_enviada < $item->cantidad_solicitada ? 'text-amber-600 font-semibold' : 'text-green-600 font-semibold' }}">
                                {{ number_format($item->cantidad_enviada, $dec) }} {{ $abbr }}
                            </span>
                            @if($item->cantidad_enviada == 0)
                                <span class="block text-xs text-red-400">No se envía</span>
                            @elseif($item->cantidad_enviada < $item->cantidad_solicitada)
                                <span class="block text-xs text-amber-500">Parcial</span>
                            @endif
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    @endif
                    @if($solicitud->esRecibida())
                    <td class="px-4 py-3.5 text-right tabular-nums">
                        @if($item->cantidad_recibida !== null)
                            <span class="{{ $item->cantidad_recibida < $item->cantidad_enviada ? 'text-amber-600 font-semibold' : 'text-green-600 font-semibold' }}">
                                {{ number_format($item->cantidad_recibida, $dec) }} {{ $abbr }}
                            </span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    @endif
                    @if(!$solicitud->esPendiente() && !$solicitud->esRecibida())
                    <td class="px-4 py-3.5 text-right tabular-nums text-gray-400 text-xs">
                        {{ number_format($stockOrigen[$item->product_id] ?? 0, $dec) }} {{ $abbr }}
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Panel: PROCESAR (quien envía) --}}
    @if($puedeEnviar)
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-blue-100 bg-blue-50/50">
            <h3 class="font-semibold text-blue-800 text-sm">Procesar solicitud — ¿qué mandas?</h3>
            <p class="text-xs text-blue-500 mt-0.5">Indica cuántos litros envías de cada producto. Pon 0 si no mandas algo.</p>
        </div>
        <form method="POST" action="{{ route('empleados.solicitudes.procesar', $solicitud) }}" class="p-5 space-y-4">
            @csrf
            @if($errors->any())
            <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
            @endif

            <div class="space-y-2">
                @foreach($solicitud->items as $item)
                @php
                    $iUnit = $item->product->unit;
                    $iDec  = \App\Support\Units::decimals($iUnit);
                    $iAbbr = \App\Support\Units::abbr($iUnit);
                    $iStep = \App\Support\Units::isDecimal($iUnit) ? '0.001' : '1';
                @endphp
                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                <div class="grid grid-cols-12 gap-3 items-center bg-gray-50 rounded-xl px-4 py-3">
                    <div class="col-span-5">
                        <p class="text-sm font-medium text-gray-800">{{ $item->product->name }}</p>
                        <p class="text-xs text-gray-400">Piden: {{ number_format($item->cantidad_solicitada, $iDec) }} {{ $iAbbr }}</p>
                    </div>
                    <div class="col-span-4 text-right">
                        <p class="text-xs text-gray-400">Tienes en almacén</p>
                        <p class="text-sm font-semibold {{ ($stockOrigen[$item->product_id] ?? 0) > 0 ? 'text-gray-700' : 'text-red-500' }}">
                            {{ number_format($stockOrigen[$item->product_id] ?? 0, $iDec) }} {{ $iAbbr }}
                        </p>
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-400 mb-1">Envías</label>
                        <input type="number"
                               name="items[{{ $loop->index }}][cantidad_enviada]"
                               value="{{ old("items.{$loop->index}.cantidad_enviada", $item->cantidad_solicitada) }}"
                               step="{{ $iStep }}" min="0"
                               max="{{ $stockOrigen[$item->product_id] ?? 0 }}"
                               class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Notas de envío (opcional)</label>
                <textarea name="notas_envio" rows="2" maxlength="500"
                          placeholder="Ej: El producto X está agotado, se envía en próximo despacho..."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-none">{{ old('notas_envio') }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    Confirmar envío
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Panel: RECIBIR (quien solicitó) --}}
    @if($puedeRecibir)
    <div class="bg-white rounded-2xl border border-green-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-green-100 bg-green-50/50">
            <h3 class="font-semibold text-green-800 text-sm">Confirmar recepción — ¿qué llegó?</h3>
            <p class="text-xs text-green-500 mt-0.5">Anota cuánto recibiste físicamente de cada producto.</p>
        </div>
        <form method="POST" action="{{ route('empleados.solicitudes.recibir', $solicitud) }}" class="p-5 space-y-4">
            @csrf
            @if($errors->any())
            <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
            @endif

            <div class="space-y-2">
                @foreach($solicitud->items as $item)
                @php
                    $rUnit = $item->product->unit;
                    $rDec  = \App\Support\Units::decimals($rUnit);
                    $rAbbr = \App\Support\Units::abbr($rUnit);
                    $rStep = \App\Support\Units::isDecimal($rUnit) ? '0.001' : '1';
                @endphp
                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                <div class="grid grid-cols-12 gap-3 items-center bg-gray-50 rounded-xl px-4 py-3">
                    <div class="col-span-5">
                        <p class="text-sm font-medium text-gray-800">{{ $item->product->name }}</p>
                        <p class="text-xs text-gray-400">Pedido: {{ number_format($item->cantidad_solicitada, $rDec) }} {{ $rAbbr }}</p>
                    </div>
                    <div class="col-span-4 text-right">
                        <p class="text-xs text-gray-400">Enviaron</p>
                        <p class="text-sm font-semibold text-blue-700">
                            {{ number_format($item->cantidad_enviada ?? 0, $rDec) }} {{ $rAbbr }}
                        </p>
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-400 mb-1">Recibiste</label>
                        <input type="number"
                               name="items[{{ $loop->index }}][cantidad_recibida]"
                               value="{{ old("items.{$loop->index}.cantidad_recibida", $item->cantidad_enviada ?? 0) }}"
                               step="{{ $rStep }}" min="0"
                               class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-right focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                    </div>
                </div>
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Notas de recepción (opcional)</label>
                <textarea name="notas_recepcion" rows="2" maxlength="500"
                          placeholder="Ej: Llegó un bote roto, faltó el producto Y..."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 resize-none">{{ old('notas_recepcion') }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold transition-colors">
                    Confirmar recepción
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Cancelar (solo si pendiente) --}}
    @if($solicitud->esPendiente())
    <div class="flex justify-end">
        <form method="POST" action="{{ route('empleados.solicitudes.cancelar', $solicitud) }}"
              onsubmit="return confirm('¿Cancelar esta solicitud?')">
            @csrf
            <button type="submit"
                    class="text-sm text-red-400 hover:text-red-600 transition-colors">
                Cancelar solicitud
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
