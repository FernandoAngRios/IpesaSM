@extends('layouts.empleados')

@section('title', 'Mensaje — IPESA SM')
@section('page-title', 'Mensaje interno')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <a href="{{ route('empleados.internal-messages.index') }}"
               class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex-1 min-w-0">
                <h2 class="font-bold text-gray-900 truncate">{{ $internalMessage->subject }}</h2>
                <p class="text-xs text-gray-400">{{ $internalMessage->created_at->format('d de F, Y \a \l\a\s H:i') }}</p>
            </div>
            @if($internalMessage->isBroadcast() && $internalMessage->sender->isAdmin())
            <span class="text-xs font-medium bg-purple-50 text-purple-600 px-2.5 py-1 rounded-full shrink-0">Para todos</span>
            @else
            <span class="text-xs font-medium {{ $internalMessage->isRead() ? 'text-gray-400' : 'text-green-600 bg-green-50 px-2.5 py-1 rounded-full' }}">
                {{ $internalMessage->isRead() ? 'Leído' : 'Nuevo' }}
            </span>
            @endif
        </div>

        {{-- Remitente --}}
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-lg shrink-0">
                    {{ strtoupper(substr($internalMessage->sender->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-900">{{ $internalMessage->sender->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        @if($internalMessage->sender->isAdmin())
                            Administrador
                        @else
                            Empleado
                        @endif
                        ·
                        @if($internalMessage->isBroadcast() && $internalMessage->sender->isAdmin())
                            Para todos los empleados
                        @elseif($internalMessage->recipient)
                            Para {{ $internalMessage->recipient->name }}
                        @else
                            Para administración
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Cuerpo --}}
        <div class="px-6 py-6">
            <p class="text-gray-800 leading-relaxed whitespace-pre-wrap text-sm">{{ $internalMessage->body }}</p>
        </div>

        {{-- Acciones --}}
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <a href="{{ route('empleados.internal-messages.create') }}"
               class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo mensaje
            </a>

            @if(auth()->user()->isAdmin())
            <form action="{{ route('empleados.internal-messages.destroy', $internalMessage) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar este mensaje?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="text-sm font-semibold text-red-600 hover:text-red-700 px-4 py-2.5 rounded-xl hover:bg-red-50 transition-colors">
                    Eliminar
                </button>
            </form>
            @endif
        </div>

    </div>
</div>
@endsection
