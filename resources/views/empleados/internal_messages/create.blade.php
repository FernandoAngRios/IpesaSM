@extends('layouts.empleados')

@section('title', 'Nuevo mensaje — IPESA SM')
@section('page-title', 'Nuevo mensaje')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <a href="{{ route('empleados.internal-messages.index') }}"
               class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-bold text-gray-900">Redactar mensaje</h2>
        </div>

        <form action="{{ route('empleados.internal-messages.store') }}" method="POST" class="px-6 py-6 space-y-5">
            @csrf

            {{-- Destinatario (solo admin) --}}
            @if(auth()->user()->isAdmin())
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Destinatario</label>
                <select name="recipient_id"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-300 bg-white">
                    <option value="">Todos los empleados</option>
                    @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ old('recipient_id') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                    @endforeach
                </select>
                @error('recipient_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @else
            <div class="flex items-center gap-2 text-sm text-gray-500 bg-gray-50 rounded-xl px-4 py-2.5">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Para: <span class="font-semibold text-gray-700">Administración</span>
            </div>
            @endif

            {{-- Asunto --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Asunto</label>
                <input type="text" name="subject" value="{{ old('subject') }}" maxlength="150"
                       placeholder="Escribe el asunto..."
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-300 @error('subject') border-red-400 @enderror">
                @error('subject')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Cuerpo --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mensaje</label>
                <textarea name="body" rows="6" maxlength="2000"
                          placeholder="Escribe tu mensaje..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-300 resize-none @error('body') border-red-400 @enderror">{{ old('body') }}</textarea>
                @error('body')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('empleados.internal-messages.index') }}"
                   class="text-sm font-semibold text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-xl hover:bg-gray-100 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Enviar
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
