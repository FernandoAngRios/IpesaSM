<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Empleados — IPESA SM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 antialiased min-h-screen flex">

    {{-- Left panel --}}
    <div class="hidden lg:flex w-1/2 relative overflow-hidden login-panel-bg">

        {{-- Overlay oscuro para legibilidad del texto --}}
        <div class="absolute inset-0 bg-black/65"></div>

        {{-- Color bar --}}
        <div class="absolute bottom-0 left-0 right-0 h-2 flex">
            @foreach(['#e63e28','#f97316','#f5c518','#2d7a4a','#3b82f6','#8b5cf6','#ec4899','#1a3c5e'] as $color)
            <div class="flex-1" style="background-color: {{ $color }}"></div>
            @endforeach
        </div>

        <div class="relative z-10 flex flex-col justify-center px-16">
            <div class="mb-12">
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl px-4 py-2 shadow-md inline-block">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="IPESA SM" class="h-12 w-auto">
                </div>
            </div>

            <h1 class="text-4xl font-black text-white leading-tight mb-4">
                Panel de<br>empleados
            </h1>
            <p class="text-white/60 text-lg leading-relaxed mb-12">
                Gestiona productos, categorías y mensajes de clientes desde aquí.
            </p>

            <div class="space-y-4">
                @foreach(['Gestión de productos y categorías', 'Consulta de mensajes de clientes', 'Acceso seguro y controlado'] as $feature)
                <div class="flex items-center gap-3 text-white/70">
                    <div class="w-5 h-5 bg-accent-500 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm">{{ $feature }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right panel (form) --}}
    <div class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">

            {{-- Mobile logo --}}
            <div class="lg:hidden flex justify-center mb-10">
                <img src="{{ asset('images/logo/logo.png') }}" alt="IPESA SM" class="h-14 w-auto">
            </div>

            <div class="mb-8">
                <h2 class="text-3xl font-black text-gray-900">Iniciar sesión</h2>
                <p class="text-gray-500 mt-1">Usa tu nombre de usuario o correo electrónico</p>
            </div>

            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                {{ session('error') }}
            </div>
            @endif

            <form action="{{ route('empleados.login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Usuario o correo electrónico</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username"
                           class="w-full border @error('login') border-red-400 @else border-gray-200 @enderror rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors bg-white"
                           placeholder="Juan Pérez  ó  correo@empresa.com">
                    @error('login')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div x-data="{ show: false }">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Contraseña</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors bg-white"
                               placeholder="••••••••">
                        <button type="button" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 accent-brand-700">
                        <span class="text-sm text-gray-600">Mantener sesión</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg hover:shadow-brand-700/20">
                    Iniciar sesión
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <a href="{{ route('landing') }}"
                   class="text-sm text-gray-500 hover:text-brand-700 transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver a la tienda
                </a>
            </div>
        </div>
    </div>

</body>
</html>
