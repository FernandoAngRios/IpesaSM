<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entrada extends Model
{
    protected $fillable = [
        'sucursal_id', 'product_id',
        'cantidad_litros', 'proveedor_nombre', 'nota', 'user_id',
    ];

    protected function casts(): array
    {
        return ['cantidad_litros' => 'decimal:3'];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
