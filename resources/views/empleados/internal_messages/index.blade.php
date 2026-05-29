@extends('layouts.empleados')

@section('title', 'Mensajes internos — IPESA SM')
@section('page-title', 'Mensajes internos')

@section('header-actions')
<a href="{{ route('empleados.internal-messages.create') }}"
   class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Nuevo mensaje
</a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    @if($messages->isEmpty())
    <div class="py-20 text-center text-gray-400">
        <div class="text-5xl mb-3">💬</div>
        <p>No hay mensajes aún</p>
    </div>
    @else
    <div class="divide-y divide-gray-50">
        @foreach($messages as $msg)
        <div class="flex items-start gap-4 px-6 py-5 hover:bg-gray-50/50 transition-colors {{ ! $msg->isRead() ? 'bg-blue-50/30' : '' }}">

            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold shrink-0">
                {{ strtoupper(substr($msg->sender->name, 0, 1)) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="font-semibold text-sm text-gray-900">{{ $msg->sender->name }}</span>
                        @if(! $msg->isRead())
                        <span class="w-2 h-2 bg-accent-500 rounded-full shrink-0"></span>
                        @endif
                        @if($msg->isBroadcast() && $msg->sender->isAdmin())
                        <span class="text-xs font-medium bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full">Todos</span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 shrink-0">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p class="text-sm font-medium text-gray-700 mt-1">{{ $msg->subject }}</p>
                <p class="text-sm text-gray-500 truncate mt-0.5">{{ $msg->body }}</p>
            </div>

            <a href="{{ route('empleados.internal-messages.show', $msg) }}"
               class="text-xs font-semibold text-brand-700 hover:text-brand-800 px-3 py-1.5 rounded-lg hover:bg-brand-50 transition-colors shrink-0">
                Ver
            </a>
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
