@php
$activeSection = match(true) {
    request()->routeIs('empleados.ventas.*', 'empleados.caja.*', 'empleados.devoluciones.*')                                                             => 'ventas',
    request()->routeIs('empleados.almacenes.*', 'empleados.entradas.*', 'empleados.solicitudes.*', 'empleados.transferencias.*', 'empleados.tintas.*')    => 'inventario',
    request()->routeIs('empleados.products.*', 'empleados.categories.*')                                                                                  => 'catalogo',
    request()->routeIs('empleados.messages.*', 'empleados.internal-messages.*')                                                                           => 'comunicacion',
    request()->routeIs('empleados.dashboard.show', 'empleados.usuarios.*', 'empleados.vendedores.*')                                                       => 'admin',
    default => null,
};

$_navUser                 = auth()->user();
$unreadContactMessages    = \App\Models\ContactMessage::unread()->count();
$unreadInternalMessages   = $_navUser->isAdmin()
    ? \App\Models\InternalMessage::forAdminInbox()->whereNull('read_at')->count()
    : \App\Models\InternalMessage::forEmployeeInbox($_navUser->id)->whereNull('read_at')->count();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel — IPESA SM')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
    <script>
        window._flash = {
            success: @json(session('success')),
            error:   @json(session('error')),
            warning: @json(session('warning')),
            info:    @json(session('info')),
        };
        window._activeNavSection = @json($activeSection);
    </script>
</head>
<body class="bg-gray-50 antialiased"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('sidebar') === '1',
          sections: {},
          toggleCollapse() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('sidebar', this.sidebarCollapsed ? '1' : '0');
          },
          toggleSection(name) {
              this.sections[name] = !this.sections[name];
              localStorage.setItem('navSections', JSON.stringify(this.sections));
          },

          // ── Toast ────────────────────────────────────────────────────
          toast:        null,
          _toastBar:    100,
          _toastTimer:  null,
          _toastAnim:   null,

          showToast(msg, type = 'success', duration = 5000) {
              clearTimeout(this._toastTimer);
              clearTimeout(this._toastAnim);
              this._toastBar = 100;
              this.toast = { msg, type, duration };
              this._toastAnim = setTimeout(() => { this._toastBar = 0; }, 60);
              this._toastTimer = setTimeout(() => this.dismissToast(), duration);
          },
          dismissToast() {
              this.toast = null;
              clearTimeout(this._toastTimer);
              clearTimeout(this._toastAnim);
          },

          init() {
              const saved = JSON.parse(localStorage.getItem('navSections') || '{}');
              this.sections = { ventas: false, inventario: false, catalogo: false, comunicacion: false, admin: false, ...saved };

              const f = window._flash;
              if (f.success) this.$nextTick(() => this.showToast(f.success, 'success'));
              else if (f.error)   this.$nextTick(() => this.showToast(f.error,   'error'));
              else if (f.warning) this.$nextTick(() => this.showToast(f.warning, 'warning'));
              else if (f.info)    this.$nextTick(() => this.showToast(f.info,    'info'));
          },
      }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-20 lg:hidden"
         @click="sidebarOpen = false"></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 bg-brand-700 text-white z-30 flex flex-col transition-all duration-300 ease-in-out overflow-hidden"
           :class="[
               sidebarCollapsed ? 'lg:w-16' : 'lg:w-64',
               sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full w-64 lg:translate-x-0'
           ]">

        {{-- Logo --}}
        <div class="shrink-0 border-b border-white/10 flex items-center justify-center transition-all duration-300"
             :class="sidebarCollapsed ? 'py-3 px-2' : 'flex-col gap-1.5 px-4 py-4'">
            <div class="bg-white/95 rounded-xl shadow-sm flex items-center justify-center overflow-hidden transition-all duration-300"
                 :class="sidebarCollapsed ? 'w-10 h-10 p-1' : 'px-4 py-2'">
                <img src="{{ asset('images/logo/logo.png') }}" alt="IPESA SM"
                     class="object-contain transition-all duration-300"
                     :class="sidebarCollapsed ? 'w-8 h-8' : 'h-14 w-auto'">
            </div>
            <p x-show="!sidebarCollapsed" x-cloak class="text-xs text-brand-300">Panel de control</p>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-4 space-y-5"
             :class="sidebarCollapsed ? 'px-2' : 'px-3'">

                {{-- Dashboard --}}
            <div>
                <a href="{{ route('empleados.dashboard.show') }}"
                   title="Dashboard"
                   class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('empleados.dashboard.show') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Dashboard</span>
                </a>
            </div>

            <div class="border-t border-white/10"></div>

                {{-- Punto de Venta — protagonista --}}
            <div>
                <a href="{{ route('empleados.pos.index') }}"
                   title="Punto de Venta"
                   class="flex items-center gap-3 py-3 rounded-xl text-sm font-bold transition-colors
                          {{ request()->routeIs('empleados.pos.*') ? 'bg-white/20 text-white' : 'text-white/90 hover:bg-white/15 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Punto de Venta</span>
                </a>
            </div>

            {{-- Ventas --}}
            <div>
                <button x-show="!sidebarCollapsed" x-cloak
                        @click="toggleSection('ventas')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-1 text-xs font-semibold text-white/80 uppercase tracking-widest hover:text-white transition-colors cursor-pointer">
                    <span>Ventas</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 opacity-60" :class="sections.ventas ? 'rotate-0' : '-rotate-90'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || sections.ventas"
                     x-transition:enter="transition-opacity ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="space-y-0.5">
                    <a href="{{ route('empleados.ventas.index') }}" title="Historial de Ventas"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.ventas.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Historial de Ventas</span>
                    </a>
                    <a href="{{ route('empleados.caja.index') }}" title="Caja"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.caja.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Caja</span>
                    </a>
                    <a href="{{ route('empleados.devoluciones.index') }}" title="Devoluciones"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.devoluciones.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Devoluciones</span>
                    </a>
                </div>
            </div>

            {{-- Inventario --}}
            <div>
                <button x-show="!sidebarCollapsed" x-cloak
                        @click="toggleSection('inventario')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-1 text-xs font-semibold text-white/80 uppercase tracking-widest hover:text-white transition-colors cursor-pointer">
                    <span>Inventario</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 opacity-60" :class="sections.inventario ? 'rotate-0' : '-rotate-90'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || sections.inventario"
                     x-transition:enter="transition-opacity ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="space-y-0.5">
                    <a href="{{ route('empleados.almacenes.index') }}" title="Almacenes"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.almacenes.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Almacenes</span>
                    </a>
                    <a href="{{ route('empleados.entradas.index') }}" title="Entradas de material"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.entradas.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Entradas de material</span>
                    </a>
                    <a href="{{ route('empleados.solicitudes.index') }}" title="Solicitudes de mercancía"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.solicitudes.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Solicitudes</span>
                    </a>
                    <a href="{{ route('empleados.transferencias.index') }}" title="Transferencias"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.transferencias.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Transferencias</span>
                    </a>
                    <a href="{{ route('empleados.tintas.index') }}" title="Tintas"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.tintas.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Tintas</span>
                    </a>
                </div>
            </div>

            {{-- Catálogo --}}
            <div>
                <button x-show="!sidebarCollapsed" x-cloak
                        @click="toggleSection('catalogo')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-1 text-xs font-semibold text-white/80 uppercase tracking-widest hover:text-white transition-colors cursor-pointer">
                    <span>Catálogo</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 opacity-60" :class="sections.catalogo ? 'rotate-0' : '-rotate-90'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || sections.catalogo"
                     x-transition:enter="transition-opacity ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="space-y-0.5">
                    <a href="{{ route('empleados.products.index') }}" title="Productos"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.products.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Productos</span>
                    </a>
                    <a href="{{ route('empleados.categories.index') }}" title="Categorías"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.categories.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Categorías</span>
                    </a>
                </div>
            </div>

            {{-- Comunicación --}}
            <div>
                <button x-show="!sidebarCollapsed" x-cloak
                        @click="toggleSection('comunicacion')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-1 text-xs font-semibold text-white/80 uppercase tracking-widest hover:text-white transition-colors cursor-pointer">
                    <span>Comunicación</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 opacity-60" :class="sections.comunicacion ? 'rotate-0' : '-rotate-90'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || sections.comunicacion"
                     x-transition:enter="transition-opacity ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="space-y-0.5">
                    <a href="{{ route('empleados.messages.index') }}" title="Mensajes clientes"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.messages.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <span class="relative shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            @if($unreadContactMessages > 0)
                            <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full ring-1 ring-brand-700"></span>
                            @endif
                        </span>
                        <span x-show="!sidebarCollapsed" x-cloak class="flex-1 truncate">Mensajes clientes</span>
                        @if($unreadContactMessages > 0)
                        <span x-show="!sidebarCollapsed" x-cloak
                              class="shrink-0 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center leading-none">
                            {{ $unreadContactMessages > 99 ? '99+' : $unreadContactMessages }}
                        </span>
                        @endif
                    </a>
                    <a href="{{ route('empleados.internal-messages.index') }}" title="Mensajes internos"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.internal-messages.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <span class="relative shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            @if($unreadInternalMessages > 0)
                            <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full ring-1 ring-brand-700"></span>
                            @endif
                        </span>
                        <span x-show="!sidebarCollapsed" x-cloak class="flex-1 truncate">Mensajes internos</span>
                        @if($unreadInternalMessages > 0)
                        <span x-show="!sidebarCollapsed" x-cloak
                              class="shrink-0 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center leading-none">
                            {{ $unreadInternalMessages > 99 ? '99+' : $unreadInternalMessages }}
                        </span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- Administración (solo admin) --}}
            @if(auth()->user()->isAdmin())
            <div>
                <button x-show="!sidebarCollapsed" x-cloak
                        @click="toggleSection('admin')"
                        class="w-full flex items-center justify-between px-3 py-1 mb-1 text-xs font-semibold text-white/80 uppercase tracking-widest hover:text-white transition-colors cursor-pointer">
                    <span>Administración</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 opacity-60" :class="sections.admin ? 'rotate-0' : '-rotate-90'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="sidebarCollapsed || sections.admin"
                     x-transition:enter="transition-opacity ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="space-y-0.5">
                    <a href="{{ route('empleados.dashboard') }}" title="Dashboard"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.dashboard') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Dashboard</span>
                    </a>
                    <a href="{{ route('empleados.usuarios.index') }}" title="Usuarios"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.usuarios.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Usuarios</span>
                    </a>
                    <a href="{{ route('empleados.vendedores.index') }}" title="Vendedores"
                       class="flex items-center gap-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('empleados.vendedores.*') ? 'bg-white/15 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span x-show="!sidebarCollapsed" x-cloak class="truncate">Vendedores</span>
                    </a>
                </div>
            </div>
            @endif

        </nav>

        {{-- Botón colapsar (solo desktop) --}}
        <div class="shrink-0 border-t border-white/10 hidden lg:flex"
             :class="sidebarCollapsed ? 'justify-center p-3' : 'p-3'">
            <button @click="toggleCollapse()" type="button"
                    class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-brand-300 hover:bg-white/10 hover:text-white transition-colors text-xs font-semibold"
                    :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'"
                    :title="sidebarCollapsed ? 'Expandir barra' : 'Colapsar barra'">
                <svg class="w-4 h-4 shrink-0 transition-transform duration-300"
                     :class="sidebarCollapsed ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak>Colapsar</span>
            </button>
        </div>

        {{-- Crédito Rio Digital --}}
        <div class="px-3 pb-4 pt-3 border-t border-white/10">
            <a href="https://riodigital.mx" target="_blank" rel="noopener"
               title="Desarrollado por Rio Digital"
               class="flex items-center gap-2.5 bg-white/95 hover:bg-white rounded-xl px-3 py-2.5 transition-all duration-200 group opacity-60 hover:opacity-100">
                <img src="{{ asset('images/RioDigital/Icono.jpeg') }}"
                     alt="Rio Digital"
                     class="w-7 h-7 rounded-lg shrink-0 object-cover">
                <span x-show="!sidebarCollapsed" x-cloak
                      class="leading-tight">
                    <span class="block text-[10px] text-gray-400 uppercase tracking-widest">Desarrollado por</span>
                    <span class="block text-xs font-semibold text-gray-700 group-hover:text-gray-900">Rio Digital</span>
                </span>
            </a>
        </div>

    </aside>

    {{-- Main content --}}
    <div class="min-h-screen flex flex-col transition-all duration-300 ease-in-out"
         :class="sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'">

        {{-- Top bar --}}
        <header class="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 lg:px-8 py-4 flex items-center gap-4">
            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-lg font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
            <div class="ml-auto flex items-center gap-3">
                @yield('header-actions')
                <a href="{{ route('landing') }}" target="_blank"
                   class="text-sm text-gray-500 hover:text-brand-700 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Ver tienda
                </a>

                <div class="h-5 w-px bg-gray-200"></div>

                {{-- Usuario dropdown --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open"
                            class="flex items-center gap-2.5 px-2 py-1.5 rounded-xl hover:bg-gray-100 transition-colors">
                        <div class="w-7 h-7 bg-brand-700 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-semibold text-gray-900 leading-none">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-400 capitalize mt-0.5">{{ auth()->user()->role }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl border border-gray-100 shadow-lg overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-xs font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <form action="{{ route('empleados.logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 lg:px-8 py-6">
            @yield('content')
        </main>
    </div>

    {{-- ── Toast global ────────────────────────────────────────────────────── --}}
    <div class="fixed top-5 right-5 z-[200] w-full max-w-xs pointer-events-none"
         x-show="toast"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-6 scale-95"
         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
         x-transition:leave-end="opacity-0 translate-x-6 scale-95"
         style="display:none">

        <div class="pointer-events-auto bg-white rounded-2xl shadow-2xl shadow-black/10 overflow-hidden border-l-[5px]"
             :class="{
                 'border-green-500': toast?.type === 'success',
                 'border-red-500':   toast?.type === 'error',
                 'border-amber-500': toast?.type === 'warning',
                 'border-blue-500':  toast?.type === 'info',
             }">

            <div class="flex items-start gap-3 px-4 pt-4 pb-3.5">

                {{-- Ícono --}}
                <div class="shrink-0 w-9 h-9 rounded-xl flex items-center justify-center mt-0.5"
                     :class="{
                         'bg-green-50 text-green-600': toast?.type === 'success',
                         'bg-red-50 text-red-600':     toast?.type === 'error',
                         'bg-amber-50 text-amber-600': toast?.type === 'warning',
                         'bg-blue-50 text-blue-600':   toast?.type === 'info',
                     }">
                    <template x-if="toast?.type === 'success'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <template x-if="toast?.type === 'error'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </template>
                    <template x-if="toast?.type === 'warning'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </template>
                    <template x-if="toast?.type === 'info'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                </div>

                {{-- Texto --}}
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-widest"
                       :class="{
                           'text-green-600': toast?.type === 'success',
                           'text-red-600':   toast?.type === 'error',
                           'text-amber-600': toast?.type === 'warning',
                           'text-blue-600':  toast?.type === 'info',
                       }"
                       x-text="{ success: 'Éxito', error: 'Error', warning: 'Atención', info: 'Info' }[toast?.type]"></p>
                    <p class="text-sm text-gray-700 mt-0.5 leading-snug" x-text="toast?.msg"></p>
                </div>

                {{-- Cerrar --}}
                <button @click="dismissToast()" type="button"
                        class="shrink-0 w-7 h-7 -mt-0.5 -mr-1 flex items-center justify-center rounded-lg text-gray-300 hover:text-gray-500 hover:bg-gray-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

            </div>

            {{-- Barra de progreso --}}
            <div class="h-0.5 w-full"
                 :class="{
                     'bg-green-100': toast?.type === 'success',
                     'bg-red-100':   toast?.type === 'error',
                     'bg-amber-100': toast?.type === 'warning',
                     'bg-blue-100':  toast?.type === 'info',
                 }">
                <div class="h-full"
                     :class="{
                         'bg-green-500': toast?.type === 'success',
                         'bg-red-500':   toast?.type === 'error',
                         'bg-amber-500': toast?.type === 'warning',
                         'bg-blue-500':  toast?.type === 'info',
                     }"
                     :style="`width: ${_toastBar}%; transition: width ${toast?.duration ?? 5000}ms linear`"></div>
            </div>

        </div>
    </div>

    {{-- ── Modal de apertura de caja (aparece una vez al iniciar sesión) ── --}}
    @if(session('caja_prompt'))
    @php
        session()->forget('caja_prompt');
        $cajasSucursalIds = \App\Models\Caja::where('estado', 'abierta')->pluck('sucursal_id');
        $user = auth()->user();
        $sucursalesSinCaja = $user->isAdmin()
            ? \App\Models\Sucursal::where('activo', true)->whereNotIn('id', $cajasSucursalIds)->orderBy('nombre')->get()
            : $user->sucursales()->where('activo', true)->whereNotIn('id', $cajasSucursalIds)->get();
    @endphp
    @if($sucursalesSinCaja->isNotEmpty())
    <div x-data="{ show: true }"
         x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none">

        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="show = false"></div>

        <div x-show="show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-5">

            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-bold text-gray-900 text-base">Abrir caja</h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        @if($sucursalesSinCaja->count() === 1)
                            {{ $sucursalesSinCaja->first()->nombre }} no tiene caja abierta hoy.
                        @else
                            {{ $sucursalesSinCaja->count() }} almacenes sin caja abierta.
                        @endif
                    </p>
                </div>
                <button @click="show = false" type="button"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('empleados.caja.abrir') }}" class="space-y-4">
                @csrf
                @if($sucursalesSinCaja->count() === 1)
                <input type="hidden" name="sucursal_id" value="{{ $sucursalesSinCaja->first()->id }}">
                @else
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Almacén</label>
                    <select name="sucursal_id" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-700/20 focus:border-brand-700">
                        @foreach($sucursalesSinCaja as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Fondo inicial en caja</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">$</span>
                        <input type="number" name="saldo_inicial" step="0.01" min="0" value="0" required
                               class="w-full border-2 border-gray-200 focus:border-brand-700 rounded-xl pl-8 pr-4 py-3 text-lg font-bold text-gray-900 focus:outline-none transition-colors">
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="show = false"
                            class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                        Después
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold transition-colors">
                        Abrir caja
                    </button>
                </div>
            </form>

        </div>
    </div>
    @endif
    @endif

    @stack('scripts')
</body>
</html>
