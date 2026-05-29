<?php

namespace App\Support;

class Units
{
    /** Todas las unidades disponibles [valor => etiqueta] */
    public static function all(): array
    {
        return [
            'litro' => 'Litro',
            'pieza' => 'Pieza',
            'kg'    => 'Kilogramo (kg)',
            'rollo' => 'Rollo',
            'lata'  => 'Lata',
            'm2'    => 'Metro cuadrado (m²)',
            'caja'  => 'Caja',
        ];
    }

    /** Etiqueta legible para una unidad */
    public static function label(string $unit): string
    {
        return self::all()[$unit] ?? $unit;
    }

    /** Abreviatura corta para tablas */
    public static function abbr(string $unit): string
    {
        return self::abbrs()[$unit] ?? $unit;
    }

    /** Mapa unidad → abreviatura, exportable como JSON para Alpine.js */
    public static function abbrs(): array
    {
        return [
            'litro' => 'L',
            'pieza' => 'pza',
            'kg'    => 'kg',
            'rollo' => 'rollo',
            'lata'  => 'lata',
            'm2'    => 'm²',
            'caja'  => 'caja',
        ];
    }

    /** Unidades que admiten cantidades decimales (fracciones) */
    public static function isDecimal(string $unit): bool
    {
        return in_array($unit, ['litro', 'kg', 'm2']);
    }

    /** Decimales a usar al formatear cantidades de esta unidad */
    public static function decimals(string $unit): int
    {
        return self::isDecimal($unit) ? 3 : 0;
    }

    /** Unidades decimales como array, para pasar a Alpine.js */
    public static function decimalUnits(): array
    {
        return array_keys(array_filter(
            self::all(),
            fn($_, $unit) => self::isDecimal($unit),
            ARRAY_FILTER_USE_BOTH
        ));
    }
}
