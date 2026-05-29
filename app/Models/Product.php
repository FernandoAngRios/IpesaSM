<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SucursalProducto;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'codigo_barras', 'slug', 'description', 'short_description',
        'price', 'costo_compra', 'unidad_compra', 'porcentaje_ganancia',
        'coverage', 'available_colors', 'image', 'unit', 'featured', 'active', 'stock_litros',
    ];

    protected function casts(): array
    {
        return [
            'available_colors' => 'array',
            'featured'    => 'boolean',
            'active'      => 'boolean',
            'price' => 'decimal:2',
            'costo_compra' => 'decimal:2',
            'porcentaje_ganancia' => 'decimal:2',
            'coverage' => 'decimal:2',
            'stock_litros' => 'decimal:3',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function presentations(): HasMany
    {
        return $this->hasMany(ProductPresentation::class)->orderBy('litros');
    }

    public function inventario(): HasMany
    {
        return $this->hasMany(SucursalProducto::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }
}
