<?php

namespace App\Exports;

use App\Models\Venta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VentasExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(
        private ?string $desde,
        private ?string $hasta,
        private ?int    $sucursalId,
        private bool    $soloAdmin,
        private array   $sucursalIds = [],
    ) {}

    public function collection(): Collection
    {
        $query = Venta::cerrada()
            ->with(['sucursal', 'user', 'items', 'pagos'])
            ->orderBy('created_at');

        if ($this->soloAdmin && $this->sucursalId) {
            $query->where('sucursal_id', $this->sucursalId);
        } elseif (!$this->soloAdmin && count($this->sucursalIds)) {
            $query->whereIn('sucursal_id', $this->sucursalIds);
        }

        if ($this->desde) {
            $query->whereDate('created_at', '>=', $this->desde);
        }

        if ($this->hasta) {
            $query->whereDate('created_at', '<=', $this->hasta);
        }

        return $query->get()->map(function (Venta $v) {
            $metodoPago = $v->pagos->map(fn($p) => ucfirst($p->metodo))->unique()->join(', ');

            return [
                'folio'        => $v->id,
                'fecha'        => $v->created_at->format('d/m/Y H:i'),
                'sucursal'     => $v->sucursal?->nombre ?? '—',
                'cliente'      => $v->cliente_nombre ?: 'Público general',
                'telefono'     => $v->cliente_telefono ?: '',
                'vendedor'     => $v->vendedor ?: ($v->user?->name ?? ''),
                'productos'    => $v->items->count(),
                'descuento'    => (float) $v->descuento,
                'total'        => (float) $v->total,
                'metodo_pago'  => $metodoPago ?: '—',
                'registrado_por' => $v->user?->name ?? '—',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Folio', 'Fecha', 'Sucursal', 'Cliente', 'Teléfono',
            'Vendedor', 'Productos', 'Descuento ($)', 'Total ($)',
            'Método de pago', 'Registrado por',
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
            'A' => 10, 'B' => 18, 'C' => 20, 'D' => 24, 'E' => 16,
            'F' => 20, 'G' => 12, 'H' => 14, 'I' => 14, 'J' => 20, 'K' => 20,
        ];
    }
}
