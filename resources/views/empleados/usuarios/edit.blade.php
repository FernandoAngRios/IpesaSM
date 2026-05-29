@extends('layouts.empleados')

@section('title', 'Editar Usuario — IPESA SM')
@section('page-title', 'Editar usuario')

@section('content')
<div class="max-w-xl mx-auto">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
        <a href="{{ route('empleados.usuarios.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h2 class="font-bold text-gray-900">{{ $user->name }}</h2>
            <p class="text-xs text-gray-400">Editar información del usuario</p>
        </div>
    </div>

    <form action="{{ route('empleados.usuarios.update', $user) }}" method="POST">
        @csrf @method('PUT')

        <div class="px-6 py-6 space-y-5">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nombre *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full border @error('name') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nueva contraseña <span class="text-gray-400 font-normal">(opcional)</span></label>
                <div class="relative">
                    <input type="password" name="password" id="edit_password"
                           class="w-full border @error('password') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                           placeholder="Dejar en blanco para no cambiar">
                    <button type="button" onclick="togglePwd('edit_password', this)"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition-colors" tabindex="-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmar contraseña</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="edit_password_confirmation"
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                    <button type="button" onclick="togglePwd('edit_password_confirmation', this)"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition-colors" tabindex="-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Rol *</label>
                <select name="role" required
                        class="w-full border @error('role') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                    <option value="employee" {{ old('role', $user->role) === 'employee' ? 'selected' : '' }}>Empleado</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador</option>
                </select>
                @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            @if($sucursales->isNotEmpty())
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Sucursales asignadas</label>
                <p class="text-xs text-gray-400 mb-2">El admin tiene acceso a todas automáticamente.</p>
                <div class="rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
                    @foreach($sucursales as $sucursal)
                    @php $asignada = $user->sucursales->contains('id', $sucursal->id); @endphp
                    <label class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="checkbox" name="sucursales[]" value="{{ $sucursal->id }}"
                               {{ in_array($sucursal->id, old('sucursales', $user->sucursales->pluck('id')->toArray())) ? 'checked' : '' }}
                               class="w-4 h-4 rounded accent-brand-700">
                        <span class="text-sm font-medium text-gray-700">{{ $sucursal->nombre }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
                <label class="flex items-center justify-between gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">Puede editar precios</p>
                        <p class="text-xs text-gray-400">Permite modificar costos, márgenes y presentaciones</p>
                    </div>
                    <input type="checkbox" name="can_edit_prices" value="1"
                           {{ old('can_edit_prices', $user->can_edit_prices) ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-brand-700">
                </label>
                <label class="flex items-center justify-between gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">Cuenta activa</p>
                        <p class="text-xs text-gray-400">El usuario puede iniciar sesión</p>
                    </div>
                    <input type="checkbox" name="active" value="1"
                           {{ old('active', $user->active) ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-brand-700">
                </label>
            </div>

        </div>

        <div class="border-t border-gray-100 px-6 py-4 flex items-center justify-end gap-3 bg-gray-50/40">
            <a href="{{ route('empleados.usuarios.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 transition-colors">Cancelar</a>
            <button type="submit"
                    class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors">
                Guardar cambios
            </button>
        </div>

    </form>
</div>
</div>
@endsection

@push('scripts')
<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    btn.querySelector('svg').style.opacity = isPassword ? '0.4' : '1';
}
</script>
@endpush
