<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SucursalProducto extends Model
{
    protected $table = 'sucursal_producto';

    protected $fillable = ['sucursal_id', 'product_id', 'stock_litros'];

    protected function casts(): array
    {
        return ['stock_litros' => 'decimal:3'];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
