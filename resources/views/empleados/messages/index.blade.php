@extends('layouts.empleados')

@section('title', 'Mensajes — IPESA SM')
@section('page-title', 'Mensajes de clientes')

@section('content')
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    @if($messages->isEmpty())
    <div class="py-20 text-center text-gray-400">
        <div class="text-5xl mb-3">✉️</div>
        <p>No hay mensajes aún</p>
    </div>
    @else
    <div class="divide-y divide-gray-50">
        @foreach($messages as $msg)
        <div class="flex items-start gap-4 px-6 py-5 hover:bg-gray-50/50 transition-colors {{ ! $msg->isRead() ? 'bg-blue-50/30' : '' }}">

            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold shrink-0">
                {{ strtoupper(substr($msg->name, 0, 1)) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="font-semibold text-sm text-gray-900">{{ $msg->name }}</span>
                        @if(! $msg->isRead())
                        <span class="w-2 h-2 bg-accent-500 rounded-full shrink-0"></span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 shrink-0">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p class="text-xs text-gray-500 mb-1">{{ $msg->email }} {{ $msg->phone ? '· '.$msg->phone : '' }}</p>
                <p class="text-sm font-medium text-gray-700">{{ $msg->subject }}</p>
                <p class="text-sm text-gray-500 truncate mt-0.5">{{ $msg->message }}</p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('empleados.messages.show', $msg) }}"
                   class="text-xs font-semibold text-brand-700 hover:text-brand-800 px-3 py-1.5 rounded-lg hover:bg-brand-50 transition-colors">
                    Ver
                </a>
                <form action="{{ route('empleados.messages.destroy', $msg) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar este mensaje?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-xs font-semibold text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    @if($messages->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $messages->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
