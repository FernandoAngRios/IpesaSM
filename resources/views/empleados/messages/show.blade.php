@extends('layouts.empleados')

@section('title', 'Mensaje — IPESA SM')
@section('page-title', 'Detalle del mensaje')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <a href="{{ route('empleados.messages.index') }}"
               class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex-1">
                <h2 class="font-bold text-gray-900">{{ $message->subject }}</h2>
                <p class="text-xs text-gray-400">Recibido {{ $message->created_at->format('d de F, Y \a \l\a\s H:i') }}</p>
            </div>
            <span class="text-xs font-medium {{ $message->isRead() ? 'text-gray-400' : 'text-green-600 bg-green-50 px-2.5 py-1 rounded-full' }}">
                {{ $message->isRead() ? 'Leído' : 'Nuevo' }}
            </span>
        </div>

        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-lg shrink-0">
                    {{ strtoupper(substr($message->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-900">{{ $message->name }}</p>
                    <div class="flex flex-wrap gap-4 mt-1">
                        <a href="mailto:{{ $message->email }}" class="text-sm text-brand-700 hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $message->email }}
                        </a>
                        @if($message->phone)
                        <a href="tel:{{ $message->phone }}" class="text-sm text-gray-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ $message->phone }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-5">
            <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $message->message }}</p>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}"
               class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                Responder por correo
            </a>

            <form action="{{ route('empleados.messages.destroy', $message) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar este mensaje?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="text-sm font-semibold text-red-600 hover:text-red-700 px-4 py-2.5 rounded-xl hover:bg-red-50 transition-colors">
                    Eliminar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
