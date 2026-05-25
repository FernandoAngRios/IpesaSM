<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return collect([[
            'Pintura Blanca Mate', 'Pinturas', 'PBM-001', 'Pintura interior de alta calidad', '',
            'litro', '12', '45.00', 'galón', '30',
            'no', 'si', 'Almacén Central', '100',
            'Litro', '1', '58.50',
            'Galón', '4', '220.00',
            'Cubeta', '19', '950.00',
        ]]);
    }

    public function headings(): array
    {
        return [
            'nombre', 'categoria', 'codigo_barras', 'descripcion_corta', 'descripcion',
            'unidad', 'rendimiento_m2', 'costo_compra', 'unidad_compra', 'porcentaje_ganancia',
            'destacado', 'activo', 'almacen', 'stock_inicial',
            'pres1_nombre', 'pres1_cantidad', 'pres1_precio',
            'pres2_nombre', 'pres2_cantidad', 'pres2_precio',
            'pres3_nombre', 'pres3_cantidad', 'pres3_precio',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1:1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
        ]);

        $sheet->getStyle('2:2')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28, 'B' => 16, 'C' => 16, 'D' => 28, 'E' => 28,
            'F' => 12, 'G' => 14, 'H' => 14, 'I' => 14, 'J' => 20,
            'K' => 12, 'L' => 10, 'M' => 22, 'N' => 14,
            'O' => 16, 'P' => 14, 'Q' => 14,
            'R' => 16, 'S' => 14, 'T' => 14,
            'U' => 16, 'V' => 14, 'W' => 14,
        ];
    }
}
