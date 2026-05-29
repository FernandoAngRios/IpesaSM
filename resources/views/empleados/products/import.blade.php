@extends('layouts.empleados')

@section('title', 'Importar Productos — IPESA SM')
@section('page-title', 'Importar Productos desde Excel')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Resultados de importación previa --}}
    @if(session('import_success'))
    <div class="bg-green-50 border border-green-200 rounded-2xl px-5 py-4">
        <p class="text-sm font-semibold text-green-700">{{ session('import_success') }}</p>
    </div>
    @endif

    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 space-y-2">
        <p class="text-sm font-semibold text-amber-700">Algunas filas no se importaron:</p>
        <ul class="list-disc list-inside space-y-1">
            @foreach(session('import_errors') as $error)
            <li class="text-xs text-amber-600">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Paso 1: descargar plantilla --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-3">
        <div class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-full bg-brand-700 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
            <h2 class="font-semibold text-gray-800">Descarga la plantilla</h2>
        </div>
        <p class="text-sm text-gray-500 pl-10">
            Rellena la plantilla de Excel con los datos de tus productos. No modifiques los encabezados.
        </p>
        <div class="pl-10">
            <a href="{{ route('empleados.products.template') }}"
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Descargar plantilla (.xlsx)
            </a>
        </div>
    </div>

    {{-- Columnas de referencia --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-3">
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Columnas de la plantilla</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach([
                'nombre'              => 'Requerido',
                'categoria'           => 'Opcional — crea automáticamente',
                'codigo_barras'       => 'Opcional — debe ser único',
                'descripcion_corta'   => 'Opcional',
                'descripcion'         => 'Opcional',
                'unidad'              => 'Opcional (default: litro)',
                'rendimiento_m2'      => 'Opcional — número',
                'costo_compra'        => 'Opcional — número',
                'unidad_compra'       => 'Opcional',
                'porcentaje_ganancia' => 'Opcional — número',
                'destacado'           => 'si / no',
                'activo'              => 'si / no',
                'almacen'             => 'Nombre exacto del almacén',
                'stock_inicial'       => 'Número',
                'pres1_nombre'        => 'Nombre presentación 1',
                'pres1_cantidad'      => 'Litros presentación 1',
                'pres1_precio'        => 'Precio presentación 1',
                'pres2_nombre'        => 'Nombre presentación 2',
                'pres2_cantidad'      => 'Litros presentación 2',
                'pres2_precio'        => 'Precio presentación 2',
                'pres3_nombre'        => 'Nombre presentación 3',
                'pres3_cantidad'      => 'Litros presentación 3',
                'pres3_precio'        => 'Precio presentación 3',
            ] as $col => $hint)
            <div class="flex flex-col gap-0.5 bg-gray-50 rounded-lg px-3 py-2">
                <span class="text-xs font-mono font-semibold text-brand-700">{{ $col }}</span>
                <span class="text-xs text-gray-400">{{ $hint }}</span>
            </div>
            @endforeach
        </div>

        @if($sucursales->isNotEmpty())
        <div class="mt-2 pt-4 border-t border-gray-100">
            <p class="text-xs font-semibold text-gray-500 mb-2">Almacenes disponibles (para columna <span class="font-mono text-brand-700">almacen</span>):</p>
            <div class="flex flex-wrap gap-2">
                @foreach($sucursales as $s)
                <span class="bg-brand-50 text-brand-700 text-xs font-semibold px-3 py-1 rounded-full">{{ $s->nombre }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Paso 2: subir archivo --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        <div class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-full bg-brand-700 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
            <h2 class="font-semibold text-gray-800">Sube el archivo completado</h2>
        </div>

        <form action="{{ route('empleados.products.import.store') }}" method="POST"
              enctype="multipart/form-data" class="pl-10 space-y-4">
            @csrf

            <div x-data="{ fileName: '' }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Archivo Excel (.xlsx, .xls, .csv)</label>
                <div class="flex items-center gap-3">
                    <label class="cursor-pointer">
                        <span class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/>
                            </svg>
                            Seleccionar archivo
                        </span>
                        <input type="file" name="archivo" accept=".xlsx,.xls,.csv" class="hidden"
                               @change="fileName = $event.target.files[0]?.name ?? ''">
                    </label>
                    <span class="text-sm text-gray-500" x-text="fileName || 'Ningún archivo seleccionado'"></span>
                </div>
                @error('archivo')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Importar productos
                </button>
                <a href="{{ route('empleados.products.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
