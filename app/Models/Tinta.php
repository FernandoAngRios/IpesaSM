<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tinta extends Model
{
    protected $fillable = [
        'nombre', 'descripcion', 'color_hex',
        'stock_litros', 'stock_minimo', 'activa',
    ];

    protected function casts(): array
    {
        return [
            'stock_litros' => 'decimal:3',
            'stock_minimo' => 'decimal:3',
            'activa'       => 'boolean',
        ];
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoTinta::class);
    }

    public function scopeActiva($query)
    {
        return $query->where('activa', true)->orderBy('nombre');
    }

    public function getBajoStockAttribute(): bool
    {
        return $this->stock_litros <= $this->stock_minimo;
    }
}
