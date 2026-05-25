<?php

namespace App\Exports;

use App\Models\SucursalProducto;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(
        private ?int $sucursalId,
        private array $sucursalIds = [],
    ) {}

    public function collection(): Collection
    {
        $query = SucursalProducto::with(['sucursal', 'product.category'])
            ->orderBy('sucursal_id');

        if ($this->sucursalId) {
            $query->where('sucursal_id', $this->sucursalId);
        } elseif (count($this->sucursalIds)) {
            $query->whereIn('sucursal_id', $this->sucursalIds);
        }

        return $query->get()
            ->sortBy(fn($sp) => $sp->product?->name)
            ->values()
            ->map(function (SucursalProducto $sp) {
            return [
                'sucursal'     => $sp->sucursal?->nombre ?? '—',
                'categoria'    => $sp->product?->category?->name ?? '—',
                'producto'     => $sp->product?->name ?? '—',
                'unidad'       => $sp->product?->unit ?? '—',
                'stock_litros' => (float) $sp->stock_litros,
                'costo'        => (float) ($sp->product?->costo_compra ?? 0),
                'valor_stock'  => round((float) $sp->stock_litros * (float) ($sp->product?->costo_compra ?? 0), 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Sucursal', 'Categoría', 'Producto', 'Unidad',
            'Stock (cantidad)', 'Costo unitario ($)', 'Valor en stock ($)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('1:1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22, 'B' => 18, 'C' => 30, 'D' => 12,
            'E' => 18, 'F' => 18, 'G' => 18,
        ];
    }
}
