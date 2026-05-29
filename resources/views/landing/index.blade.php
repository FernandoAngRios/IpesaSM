@extends('layouts.landing')

@section('title', 'IPESA SM — Pinturas y Recubrimientos de Alta Calidad')

@section('content')

{{-- ===== NAVBAR ===== --}}
<nav class="fixed top-0 inset-x-0 z-50 transition-all duration-300" x-data="{ scrolled: false, mobileOpen: false }"
     x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)"
     :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-sm' : 'bg-transparent'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">

            {{-- Logo --}}
            <a href="{{ route('landing') }}" class="flex items-center">
                <div class="bg-white/85 backdrop-blur-sm rounded-xl px-3 py-1 shadow-sm">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="IPESA SM" class="h-10 w-auto">
                </div>
            </a>

            {{-- Desktop Nav --}}
            <div class="hidden lg:flex items-center gap-8">
                @foreach([['#inicio','Inicio'],['#categorias','Categorías'],['#productos','Productos'],['#tecnologia','Tecnología'],['#servicios','Servicios'],['#calculadora','Calculadora'],['#contacto','Contacto']] as [$href, $label])
                <a href="{{ $href }}"
                   :class="scrolled ? 'text-gray-700 hover:text-brand-700' : ''"
                   class="nav-item text-sm font-medium transition-colors">{{ $label }}</a>
                @endforeach
            </div>

            {{-- CTA --}}
            <div class="hidden lg:flex items-center gap-4">
                <a href="{{ route('empleados.login') }}"
                   :class="scrolled ? 'text-brand-700 hover:text-brand-800' : ''"
                   class="nav-item text-sm font-medium transition-colors">Empleados</a>
                <a href="#contacto"
                   class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-md">
                    Cotizar ahora
                </a>
                <div class="h-5 w-px" :class="scrolled ? 'bg-gray-200' : 'bg-white/30'"></div>
                <a href="https://riodigital.mx" target="_blank" rel="noopener"
                   title="Desarrollado por Rio Digital"
                   class="opacity-60 hover:opacity-100 transition-opacity duration-200">
                    <img src="{{ asset('images/RioDigital/Icono.jpeg') }}"
                         alt="Rio Digital"
                         class="h-9 w-auto rounded">
                </a>
            </div>

            {{-- Mobile menu button --}}
            <button @click="mobileOpen = !mobileOpen"
                    :class="scrolled ? 'text-gray-700' : ''"
                    class="nav-btn lg:hidden p-2 rounded-lg">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileOpen" x-transition class="lg:hidden bg-white border-t border-gray-100 px-4 py-4 space-y-2">
        @foreach([['#inicio','Inicio'],['#categorias','Categorías'],['#productos','Productos'],['#tecnologia','Tecnología'],['#servicios','Servicios'],['#calculadora','Calculadora'],['#contacto','Contacto']] as [$href, $label])
        <a href="{{ $href }}" @click="mobileOpen = false"
           class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium">{{ $label }}</a>
        @endforeach
        <div class="pt-2 border-t border-gray-100">
            <a href="{{ route('empleados.login') }}" class="block px-3 py-2 text-sm text-brand-700 font-medium">Acceso empleados</a>
        </div>
    </div>
</nav>

{{-- ===== HERO ===== --}}
@php
// Placeholders hasta tener fotos reales. Ruta esperada: /images/sucursales/{slug}.jpg
$heroSlides = $sucursales->map(fn($s, $i) => [
    'sucursal' => $s->nombre,
    'image'    => '/images/sucursales/' . \Illuminate\Support\Str::slug($s->nombre) . '.jpg',
    'fallback' => ['135deg, #1f2937 0%, #374151 100%',
                   '135deg, #111827 0%, #1f2937 100%',
                   '135deg, #1a2030 0%, #2d3748 100%'][$i] ?? '135deg, #1f2937, #374151',
])->values();
@endphp

<section id="inicio" class="relative min-h-screen flex items-center overflow-hidden bg-brand-900"
         x-data="{
             current: 0,
             total: {{ $heroSlides->count() }},
             init() {
                 setInterval(() => { this.current = (this.current + 1) % this.total }, 5000)
             }
         }"
         x-init="init()">

    {{-- Slides de fondo --}}
    @foreach($heroSlides as $i => $slide)
    <div x-show="current === {{ $i }}"
         x-transition:enter="transition-opacity duration-1000 ease-in-out"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-1000 ease-in-out"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0"
         @if($i > 0) style="display:none" @endif>
        <img src="{{ $slide['image'] }}" alt="{{ $slide['sucursal'] }}"
             class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 hero-overlay"></div>
    </div>
    @endforeach

    {{-- Decoraciones de manchas de pintura --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full opacity-20 deco-paint-red"></div>
        <div class="absolute top-1/3 -left-20 w-80 h-80 rounded-full opacity-15 deco-paint-orange"></div>
        <div class="absolute -bottom-20 right-1/4 w-72 h-72 rounded-full opacity-15 deco-paint-brand"></div>
    </div>

    {{-- Contenido --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 lg:py-0">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            {{-- Texto --}}
            <div>
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur text-white/90 text-sm px-4 py-1.5 rounded-full mb-8 border border-white/20">
                    <span class="w-2 h-2 bg-brand-400 rounded-full animate-pulse"></span>
                    Más de 17 años transformando espacios
                </div>
                <h1 class="text-5xl lg:text-6xl xl:text-7xl font-black text-white leading-[1.05] mb-6 hero-text-shadow">
                    Dale color a tu
                    <span class="text-gradient-warm">mundo</span>
                </h1>
                <p class="text-white/90 text-lg lg:text-xl leading-relaxed mb-10 max-w-xl hero-text-shadow">
                    Pinturas, esmaltes e impermeabilizantes de la más alta calidad. Colores personalizados,
                    asesoría experta y los mejores precios del mercado.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="#productos" class="bg-brand-700 hover:bg-brand-800 text-white font-bold px-8 py-4 rounded-2xl transition-all shadow-lg hover:shadow-brand-700/30 hover:-translate-y-0.5">
                        Ver productos
                    </a>
                    <a href="#contacto" class="bg-white/10 hover:bg-white/20 backdrop-blur text-white font-semibold px-8 py-4 rounded-2xl transition-all border border-white/20">
                        Cotizar ahora
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-4">
                @foreach([
                    ['35+',  'Años de experiencia',  '#e8b820'],
                    ['500+', 'Colores disponibles',  '#f97316'],
                    ['10k+', 'Clientes satisfechos', '#a8202e'],
                    ['5',    'Calidades de Pinturas',   '#2d7a4a'],
                ] as [$num, $label, $color])
                <div class="bg-white/10 backdrop-blur border border-white/15 rounded-2xl p-6 text-center hover:bg-white/15 transition-all">
                    <div class="text-4xl font-black mb-1" style="color: {{ $color }}">{{ $num }}</div>
                    <div class="text-white/70 text-sm">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Indicadores de sucursal + navegación --}}
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 z-20 flex items-center gap-4">
        @foreach($heroSlides as $i => $slide)
        <button @click="current = {{ $i }}"
                class="flex items-center gap-2 transition-all duration-300 focus:outline-none group">
            <div :class="current === {{ $i }} ? 'w-8 bg-white' : 'w-2 bg-white/40 group-hover:bg-white/70'"
                 class="h-2 rounded-full transition-all duration-300"></div>
            <span x-show="current === {{ $i }}"
                  class="text-white/90 text-xs font-semibold tracking-wide whitespace-nowrap">
                {{ $slide['sucursal'] }}
            </span>
        </button>
        @endforeach
    </div>

    {{-- Barra de colores decorativa --}}
    <div class="absolute bottom-0 left-0 right-0 h-1.5 flex z-20">
        @foreach(['#d63031','#e17055','#fdcb6e','#00b894','#0984e3','#6c5ce7','#e84393','#a8202e'] as $color)
        <div class="flex-1" style="background-color: {{ $color }}"></div>
        @endforeach
    </div>

</section>

{{-- ===== CATEGORÍAS ===== --}}
<section id="categorias" class="py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <span class="text-brand-700 font-semibold text-sm uppercase tracking-widest">Nuestras líneas</span>
            <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mt-2">Categorías de productos</h2>
            <p class="text-gray-500 mt-4 max-w-2xl mx-auto">Contamos con líneas especializadas para cada necesidad, desde interiores hasta aplicaciones industriales.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            @foreach($categories as $category)
            <div class="group bg-white rounded-2xl p-5 text-center shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-100 hover:-translate-y-1">
                <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-md transition-transform group-hover:scale-110"
                     style="background-color: {{ $category->color }}; color: #fff;">
                    {!! $category->iconSvg(28) !!}
                </div>
                <h3 class="font-bold text-sm text-gray-900 leading-tight mb-1">{{ $category->name }}</h3>
                <p class="text-xs text-gray-400 leading-snug">{{ $category->products()->active()->count() }} productos</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== PRODUCTOS DESTACADOS ===== --}}
<section id="productos" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <span class="text-brand-700 font-semibold text-sm uppercase tracking-widest">Lo mejor de IPESA</span>
            <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mt-2">Productos destacados</h2>
            <p class="text-gray-500 mt-4 max-w-2xl mx-auto">Selección de nuestros productos más populares con la mejor relación calidad-precio.</p>
        </div>

        @if($featuredProducts->isNotEmpty())
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredProducts as $product)
            <div class="group bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 flex flex-col">

                {{-- Product color preview --}}
                <div class="h-44 relative overflow-hidden"
                     style="background: linear-gradient(135deg, {{ $product->category->color }}22, {{ $product->category->color }}44)">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-20 h-20 rounded-full shadow-xl flex items-center justify-center text-4xl"
                             style="background-color: {{ $product->category->color }}">
                            🪣
                        </div>
                    </div>
                    {{-- Color swatches --}}
                    @if($product->available_colors)
                    <div class="absolute bottom-3 left-3 flex gap-1.5">
                        @foreach(array_slice($product->available_colors, 0, 5) as $color)
                        <div class="w-5 h-5 rounded-full border-2 border-white shadow-sm"
                             style="background-color: {{ $color }}" title="{{ $color }}"></div>
                        @endforeach
                        @if(count($product->available_colors) > 5)
                        <div class="w-5 h-5 rounded-full border-2 border-white shadow-sm bg-gray-200 flex items-center justify-center text-[8px] font-bold text-gray-600">
                            +{{ count($product->available_colors) - 5 }}
                        </div>
                        @endif
                    </div>
                    @endif
                    <div class="absolute top-3 right-3 bg-brand-700 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                        Destacado
                    </div>
                </div>

                <div class="p-5 flex flex-col flex-1">
                    <span class="text-xs font-semibold text-accent-500 uppercase tracking-wide mb-1">
                        {{ $product->category->name }}
                    </span>
                    <h3 class="font-bold text-gray-900 text-sm mb-2 leading-snug">{{ $product->name }}</h3>
                    <p class="text-xs text-gray-500 leading-relaxed mb-4 flex-1">{{ $product->short_description }}</p>

                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-2xl font-black text-brand-700">${{ number_format($product->price, 2) }}</span>
                            <span class="text-xs text-gray-400 ml-1">/ {{ $product->unit }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500">Rinde</span>
                            <div class="text-sm font-bold text-green-600">{{ $product->coverage }} m²/L</div>
                        </div>
                    </div>

                    <a href="#contacto"
                       class="block text-center bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        Cotizar
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-16 text-gray-400">
            <div class="text-6xl mb-4">🎨</div>
            <p>Próximamente nuestros productos disponibles.</p>
        </div>
        @endif
    </div>
</section>

{{-- ===== TECNOLOGÍA ===== --}}
<section id="tecnologia" class="py-24 bg-gray-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Encabezado --}}
        <div class="text-center mb-16">
            <span class="text-brand-700 font-semibold text-sm uppercase tracking-widest">Innovación al servicio del color</span>
            <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mt-2">Tecnología de última generación</h2>
            <p class="text-gray-500 mt-4 max-w-2xl mx-auto">
                Contamos con equipos de colorimetría computerizada que reproducen cualquier color con precisión milimétrica,
                directamente en tienda y en minutos.
            </p>
        </div>

        {{-- Contenido: video + características --}}
        <div class="grid lg:grid-cols-2 gap-12 items-center">

            {{-- Video --}}
            <div class="relative rounded-3xl overflow-hidden shadow-2xl bg-black aspect-video">
                {{--
                    Reemplaza el src del iframe con la URL de tu video.
                    Ejemplo YouTube: https://www.youtube.com/embed/ID_DEL_VIDEO
                --}}
                <iframe
                    class="absolute inset-0 w-full h-full"
                    src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                    title="Tecnología de colorimetría IPESA SM"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>

            {{-- Características --}}
            <div class="space-y-6">

                <div class="flex gap-5">
                    <div class="shrink-0 w-12 h-12 rounded-2xl bg-brand-700 flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Igualación exacta de colores</h3>
                        <p class="text-gray-500 text-sm mt-1 leading-relaxed">
                            Nuestra máquina analiza cualquier muestra — una pared, una tela, una imagen — y reproduce el color con
                            una precisión de hasta 0.1 Delta E. Traenos tu muestra y en minutos tendrás el color perfecto.
                        </p>
                    </div>
                </div>

                <div class="flex gap-5">
                    <div class="shrink-0 w-12 h-12 rounded-2xl bg-accent-500 flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Listo en minutos, no en días</h3>
                        <p class="text-gray-500 text-sm mt-1 leading-relaxed">
                            El proceso completo de mezcla computerizada tarda menos de 5 minutos. Sin esperas, sin pedidos
                            especiales: tu color personalizado al momento.
                        </p>
                    </div>
                </div>

                <div class="flex gap-5">
                    <div class="shrink-0 w-12 h-12 rounded-2xl bg-green-600 flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Miles de colores del catálogo</h3>
                        <p class="text-gray-500 text-sm mt-1 leading-relaxed">
                            Accedemos a catálogos internacionales como Comex, Sherwin-Williams y Sikkens. Si el color existe
                            en el mundo, podemos mezclarlo para ti.
                        </p>
                    </div>
                </div>

                <div class="pt-2">
                    <a href="#contacto"
                       class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white font-semibold px-6 py-3 rounded-xl transition-colors shadow-md">
                        Solicita una muestra
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- ===== SERVICIOS ===== --}}
<section id="servicios" class="py-24 bg-brand-700 text-white relative overflow-hidden">

    {{-- Background decoration --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-20 -right-20 w-96 h-96 rounded-full opacity-10 deco-paint-orange"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full opacity-10 deco-paint-blue"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <span class="text-white/70 font-semibold text-sm uppercase tracking-widest">¿Por qué elegirnos?</span>
            <h2 class="text-4xl lg:text-5xl font-black mt-2">Nuestros servicios</h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach([
                ['🎨', 'Colorimetría personalizada', 'Mezclamos el color exacto que necesitas con nuestra tecnología de colorimetría de precisión. Miles de colores disponibles.'],
                ['🏠', 'Asesoría de proyecto', 'Nuestros expertos te ayudan a elegir el producto ideal según la superficie, clima y condiciones de uso de tu proyecto.'],
                ['📐', 'Cálculo de material', 'Te calculamos exactamente cuánta pintura necesitas para evitar desperdicios o quedarte corto en tu proyecto.'],
                ['🚚', 'Entrega a domicilio', 'Llevamos tu pedido directamente a tu obra o domicilio. Servicio disponible en toda la zona metropolitana.'],
                ['⭐', 'Garantía de calidad', 'Todos nuestros productos cuentan con garantía. Si no estás satisfecho, te hacemos la reposición sin costo.'],
                ['💡', 'Capacitación técnica', 'Ofrecemos talleres y asesorías para pintores y contratistas sobre técnicas de aplicación y manejo de productos.'],
            ] as [$icon, $title, $desc])
            <div class="bg-white/8 hover:bg-white/12 border border-white/10 rounded-3xl p-7 transition-all group">
                <div class="text-4xl mb-4">{{ $icon }}</div>
                <h3 class="text-lg font-bold mb-3">{{ $title }}</h3>
                <p class="text-white/65 text-sm leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== CALCULADORA DE PINTURA ===== --}}
<section id="calculadora" class="py-24 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12">
            <span class="text-brand-700 font-semibold text-sm uppercase tracking-widest">Herramienta gratuita</span>
            <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mt-2">Calculadora de pintura</h2>
            <p class="text-gray-500 mt-4">Calcula cuántos litros necesitas para tu proyecto en segundos.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden"
             x-data="{
                area: '',
                coats: 2,
                coverage: 10,
                wastage: 10,
                get liters() {
                    if (!this.area || parseFloat(this.area) <= 0) return 0;
                    const total = (parseFloat(this.area) * this.coats) / this.coverage;
                    return (total * (1 + this.wastage / 100)).toFixed(2);
                },
                get cans4L() { return Math.ceil(this.liters / 4); },
                get cans1L() { return Math.ceil(this.liters / 1); },
             }">

            <div class="grid md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100">

                {{-- Inputs --}}
                <div class="p-8 space-y-6">
                    <h3 class="font-bold text-gray-900 text-lg">Datos del área</h3>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Área total a pintar (m²)
                        </label>
                        <input type="number" x-model="area" min="0" placeholder="Ej: 50"
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                        <p class="text-xs text-gray-400 mt-1">Largo × alto de todas las paredes/superficies</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Número de manos: <span class="text-brand-700 font-bold" x-text="coats"></span>
                        </label>
                        <div class="flex gap-3">
                            @foreach([1,2,3] as $n)
                            <button @click="coats = {{ $n }}"
                                    :class="coats === {{ $n }} ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-gray-600 border-gray-200 hover:border-brand-700/50'"
                                    class="flex-1 py-2.5 rounded-xl border-2 text-sm font-bold transition-all">
                                {{ $n }} {{ $n === 1 ? 'mano' : 'manos' }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Rendimiento del producto (m²/litro)
                        </label>
                        <select x-model="coverage" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                            <option value="8">8 m²/L — Impermeabilizante / Textura</option>
                            <option value="10" selected>10 m²/L — Pintura estándar</option>
                            <option value="12">12 m²/L — Pintura vinílica premium</option>
                            <option value="14">14 m²/L — Pintura de alta cobertura</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Desperdicio estimado: <span class="text-brand-700 font-bold" x-text="wastage + '%'"></span>
                        </label>
                        <input type="range" x-model="wastage" min="5" max="25" step="5"
                               class="w-full accent-brand-700">
                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>5% (rodillo)</span>
                            <span>25% (brocha/spray)</span>
                        </div>
                    </div>
                </div>

                {{-- Result --}}
                <div class="p-8 flex flex-col justify-center calc-result-bg">
                    <h3 class="font-bold text-gray-900 text-lg mb-6">Resultado</h3>

                    <div x-show="!area || parseFloat(area) <= 0" class="text-center py-8 text-gray-400">
                        <div class="text-5xl mb-3">🪣</div>
                        <p class="text-sm">Ingresa el área para calcular</p>
                    </div>

                    <div x-show="area && parseFloat(area) > 0" class="space-y-4">
                        <div class="bg-brand-700 text-white rounded-2xl p-6 text-center">
                            <div class="text-5xl font-black" x-text="liters + ' L'"></div>
                            <div class="text-brand-200 text-sm mt-1">litros necesarios</div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white rounded-xl p-4 text-center border border-gray-100 shadow-sm">
                                <div class="text-2xl font-black text-brand-700" x-text="cans4L"></div>
                                <div class="text-xs text-gray-500 mt-1">cubetas de 4L</div>
                            </div>
                            <div class="bg-white rounded-xl p-4 text-center border border-gray-100 shadow-sm">
                                <div class="text-2xl font-black text-brand-700" x-text="cans1L"></div>
                                <div class="text-xs text-gray-500 mt-1">litros sueltos</div>
                            </div>
                        </div>

                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700">
                            💡 Esta es una estimación. Te recomendamos comprar un 10–15% adicional para retoques futuros.
                        </div>

                        <a href="#contacto" class="block text-center bg-brand-700 hover:bg-brand-800 text-white font-bold py-3 rounded-xl transition-colors">
                            Solicitar cotización
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== BANNER CTA ===== --}}
<section class="py-20 relative overflow-hidden bg-brand-gradient">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 left-1/4 w-64 h-64 rounded-full opacity-20 bg-white"></div>
        <div class="absolute -bottom-16 right-1/3 w-80 h-80 rounded-full opacity-10 bg-white"></div>
    </div>
    <div class="relative max-w-4xl mx-auto px-4 text-center text-white">
        <h2 class="text-4xl lg:text-5xl font-black mb-4">¿Listo para transformar tu espacio?</h2>
        <p class="text-white/80 text-lg mb-8">Contáctanos hoy y recibe asesoría personalizada sin costo.</p>
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="#contacto" class="bg-white text-brand-700 font-bold px-8 py-4 rounded-2xl hover:bg-gray-50 transition-colors shadow-xl">
                Solicitar cotización
            </a>
            <a href="tel:+521234567890" class="bg-white/15 hover:bg-white/25 text-white font-bold px-8 py-4 rounded-2xl transition-colors border border-white/30">
                📞 Llamar ahora
            </a>
        </div>
    </div>
</section>

{{-- ===== CONTACTO ===== --}}
<section id="contacto" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12">
            <span class="text-brand-700 font-semibold text-sm uppercase tracking-widest">Estamos aquí para ti</span>
            <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mt-2">Contáctanos</h2>
            <p class="text-gray-500 mt-4">Cuéntanos tu proyecto y te asesoramos sin compromiso.</p>
        </div>

        <div x-data="{
                 sucursal: '{{ old('sucursal', $sucursales->first()?->nombre ?? '') }}',
                 datos: {
                     @foreach($sucursales as $s)
                     {{ json_encode($s->nombre) }}: {
                         image:  '/images/sucursales/{{ \Illuminate\Support\Str::slug($s->nombre) }}.jpg',
                         mapUrl: 'https://maps.google.com/maps?q={{ urlencode($s->direccion) }}&output=embed&z=16'
                     }{{ !$loop->last ? ',' : '' }}
                     @endforeach
                 },
                 get actual() { return this.datos[this.sucursal] ?? {} }
             }">

            {{-- Grid: selector | imagen+mapa | formulario --}}
            <div class="grid lg:grid-cols-3 gap-6 items-stretch">

                {{-- Col 1: Selector de sucursal --}}
                <div class="bg-brand-700 rounded-3xl p-7 flex flex-col">
                    <p class="text-xs font-bold text-white uppercase tracking-widest mb-1">Escríbenos a</p>
                    <h3 class="font-black text-white text-lg mb-1">Elige una sucursal</h3>
                    <p class="text-white text-xs mb-5 opacity-90">Selecciona la tienda a la que quieres contactar.</p>

                    <div class="flex flex-col gap-3 flex-1">
                        @foreach($sucursales as $s)
                        <button type="button"
                                @click="sucursal = '{{ $s->nombre }}'"
                                :class="sucursal === '{{ $s->nombre }}'
                                    ? 'bg-white/20 border-white'
                                    : 'bg-white/10 border-white/30 hover:bg-white/15 hover:border-white/50'"
                                class="flex-1 w-full text-left border rounded-2xl px-4 py-3 transition-all duration-200 focus:outline-none">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-bold text-white text-sm mb-0.5">{{ $s->nombre }}</p>
                                    <p class="text-white text-xs leading-relaxed opacity-90">{{ $s->direccion }}</p>
                                    <a href="tel:{{ preg_replace('/[^+\d]/', '', $s->telefono) }}"
                                       @click.stop
                                       class="inline-flex items-center gap-1 text-white text-xs font-semibold mt-1.5 hover:opacity-80 transition-opacity">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $s->telefono }}
                                    </a>
                                </div>
                                <div :class="sucursal === '{{ $s->nombre }}' ? 'border-white bg-white' : 'border-white/50'"
                                     class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5 transition-all">
                                    <div x-show="sucursal === '{{ $s->nombre }}'" class="w-1.5 h-1.5 rounded-full bg-brand-700"></div>
                                </div>
                            </div>
                        </button>
                        @endforeach
                    </div>

                    <div class="mt-5 pt-4 border-t border-white/20 space-y-2 text-xs text-white opacity-80">
                        <p class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Lun–Vie 8am–6pm · Sáb 9am–2pm
                        </p>
                        <p class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            ventas@ipesasm.com
                        </p>
                    </div>
                </div>

                {{-- Col 2: Foto + Mapa --}}
                <div class="flex flex-col gap-4 rounded-3xl overflow-hidden">

                    {{-- Foto de la sucursal --}}
                    <div class="relative rounded-2xl overflow-hidden bg-gray-200 flex-1 min-h-[140px]">
                        <img :src="actual.image" :alt="sucursal"
                             class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <p class="text-white/70 text-[10px] font-bold uppercase tracking-widest">Sucursal</p>
                            <p class="text-white font-black text-base leading-tight" x-text="sucursal"></p>
                        </div>
                    </div>

                    {{-- Mapa --}}
                    <div class="flex-1 rounded-2xl overflow-hidden border border-gray-100 shadow-sm min-h-[180px]">
                        <iframe x-effect="$el.src = actual.mapUrl"
                                class="w-full h-full border-0"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>

                {{-- Col 3: Formulario --}}
                <div class="bg-gray-50 rounded-3xl p-7 flex flex-col">

                    @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    @endif

                    <form action="{{ route('contacto.store') }}" method="POST" class="flex flex-col gap-3 flex-1">
                        @csrf
                        <input type="hidden" name="sucursal" :value="sucursal">
                        @error('sucursal')<p class="text-red-500 text-xs">Selecciona una sucursal.</p>@enderror

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full border @error('name') border-red-400 @else border-gray-200 @enderror bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                                       placeholder="Tu nombre">
                                @error('name')<p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Teléfono</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}"
                                       class="w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                                       placeholder="+52 (722) 000-0000">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Correo electrónico *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full border @error('email') border-red-400 @else border-gray-200 @enderror bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors"
                                   placeholder="tu@correo.com">
                            @error('email')<p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Asunto *</label>
                            <select name="subject" required
                                    class="w-full border @error('subject') border-red-400 @else border-gray-200 @enderror bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors">
                                <option value="">Selecciona un asunto</option>
                                @foreach(['Cotización de producto','Asesoría técnica','Colorimetría personalizada','Pedido mayoreo','Otro'] as $opt)
                                <option value="{{ $opt }}" {{ old('subject') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('subject')<p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Mensaje *</label>
                            <textarea name="message" rows="3" required
                                      class="w-full h-full min-h-[80px] border @error('message') border-red-400 @else border-gray-200 @enderror bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700 transition-colors resize-none"
                                      placeholder="Cuéntanos sobre tu proyecto...">{{ old('message') }}</textarea>
                            @error('message')<p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit"
                                class="w-full bg-brand-700 hover:bg-brand-800 text-white font-bold py-3 rounded-2xl transition-all shadow-md hover:shadow-brand-700/20 hover:-translate-y-0.5 text-sm mt-auto">
                            Enviar mensaje
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- ===== FOOTER ===== --}}
<footer class="bg-brand-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid md:grid-cols-4 gap-10">

            {{-- Brand --}}
            <div class="md:col-span-2">
                <div class="mb-4">
                    <div class="bg-white/90 rounded-xl px-3 py-1.5 inline-block">
                        <img src="{{ asset('images/logo/logo.png') }}" alt="IPESA SM" class="h-10 w-auto">
                    </div>
                </div>
                <p class="text-white/55 text-sm leading-relaxed max-w-xs">
                    Más de 17 años siendo la tienda de pinturas de confianza. Calidad, variedad y el mejor servicio para tu proyecto.
                </p>
            </div>

            {{-- Links --}}
            <div>
                <p class="font-bold text-sm mb-4">Navegación</p>
                <ul class="space-y-2">
                    @foreach([['#inicio','Inicio'],['#categorias','Categorías'],['#productos','Productos'],['#servicios','Servicios'],['#calculadora','Calculadora'],['#contacto','Contacto']] as [$href,$label])
                    <li><a href="{{ $href }}" class="text-white/55 hover:text-white text-sm transition-colors">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <p class="font-bold text-sm mb-4">Empresa</p>
                <ul class="space-y-2">
                    <li><a href="{{ route('empleados.login') }}" class="text-white/55 hover:text-white text-sm transition-colors">Portal empleados</a></li>
                    <li><span class="text-white/35 text-sm">Aviso de privacidad</span></li>
                    <li><span class="text-white/35 text-sm">Términos y condiciones</span></li>
                </ul>
            </div>
        </div>

        {{-- Color bar --}}
        <div class="my-10 h-px flex gap-0">
            @foreach(['#e63e28','#f97316','#f5c518','#2d7a4a','#3b82f6','#8b5cf6','#ec4899','#1a3c5e'] as $color)
            <div class="flex-1 h-px" style="background-color: {{ $color }}"></div>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-white/35 text-xs">
            <p>© {{ date('Y') }} IPESA SM. Todos los derechos reservados.</p>
            <a href="https://riodigital.mx" target="_blank" rel="noopener"
               class="flex items-center gap-2 opacity-75 hover:opacity-100 transition-opacity duration-200">
                <span class="text-white/50 text-xs">Desarrollado por</span>
                <img src="{{ asset('images/RioDigital/logo-Fblanco.jpeg') }}"
                     alt="Rio Digital"
                     class="h-10 w-auto rounded-md">
            </a>
        </div>
    </div>
</footer>

@endsection
