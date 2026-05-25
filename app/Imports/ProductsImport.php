<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sucursal;
use App\Models\SucursalProducto;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public int   $created = 0;
    public array $errors  = [];

    public function __construct()
    {
        HeadingRowFormatter::default('none');
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $nombre = trim($row['nombre'] ?? '');

            if ($nombre === '') continue;

            try {
                $category = Category::firstOrCreate(
                    ['name' => trim($row['categoria'] ?? '') ?: 'General'],
                    ['color' => '#6B7280', 'active' => true, 'icon' => null]
                );

                $barcode = trim($row['codigo_barras'] ?? '');
                if ($barcode !== '' && Product::where('codigo_barras', $barcode)->exists()) {
                    $this->errors[] = "Fila {$rowNum}: código «{$barcode}» ya existe — omitida.";
                    continue;
                }

                $costo = is_numeric($row['costo_compra'] ?? null)          ? (float) $row['costo_compra']          : null;
                $pct   = is_numeric($row['porcentaje_ganancia'] ?? null)    ? (float) $row['porcentaje_ganancia']   : null;
                $price = ($costo !== null && $pct !== null)                 ? round($costo * (1 + $pct / 100), 2)   : null;

                $product = Product::create([
                    'name'                => $nombre,
                    'slug'                => Str::slug($nombre),
                    'category_id'         => $category->id,
                    'codigo_barras'       => $barcode ?: null,
                    'short_description'   => trim($row['descripcion_corta'] ?? '') ?: null,
                    'description'         => trim($row['descripcion']       ?? '') ?: null,
                    'unit'                => trim($row['unidad']            ?? '') ?: 'litro',
                    'coverage'            => is_numeric($row['rendimiento_m2'] ?? null) ? (float) $row['rendimiento_m2'] : null,
                    'costo_compra'        => $costo,
                    'unidad_compra'       => trim($row['unidad_compra']     ?? '') ?: null,
                    'porcentaje_ganancia' => $pct,
                    'price'               => $price,
                    'featured'            => strtolower(trim($row['destacado'] ?? 'no')) === 'si',
                    'active'              => strtolower(trim($row['activo']    ?? 'si')) !== 'no',
                    'available_colors'    => [],
                ]);

                // Presentaciones (hasta 3)
                for ($i = 1; $i <= 3; $i++) {
                    $pNombre   = trim($row["pres{$i}_nombre"]   ?? '');
                    $pCantidad = $row["pres{$i}_cantidad"] ?? null;
                    $pPrecio   = $row["pres{$i}_precio"]   ?? null;

                    if ($pNombre !== '' && is_numeric($pCantidad) && (float) $pCantidad > 0) {
                        $product->presentations()->create([
                            'nombre' => $pNombre,
                            'litros' => (float) $pCantidad,
                            'precio' => is_numeric($pPrecio) ? (float) $pPrecio : 0,
                        ]);
                    }
                }

                // Stock inicial en almacén
                $almacenNombre = trim($row['almacen']       ?? '');
                $stockInicial  = $row['stock_inicial'] ?? null;

                if ($almacenNombre !== '' && is_numeric($stockInicial) && (float) $stockInicial > 0) {
                    $sucursal = Sucursal::whereRaw('LOWER(nombre) = ?', [strtolower($almacenNombre)])->first();

                    if ($sucursal) {
                        SucursalProducto::create([
                            'sucursal_id'  => $sucursal->id,
                            'product_id'   => $product->id,
                            'stock_litros' => (float) $stockInicial,
                        ]);
                    } else {
                        $this->errors[] = "Fila {$rowNum}: almacén «{$almacenNombre}» no encontrado — stock no registrado.";
                    }
                }

                $this->created++;

            } catch (\Throwable $e) {
                $this->errors[] = "Fila {$rowNum}: {$e->getMessage()}";
            }
        }
    }
}
