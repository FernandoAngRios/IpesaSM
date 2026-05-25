<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPresentation extends Model
{
    protected $fillable = ['product_id', 'nombre', 'litros', 'precio'];

    protected function casts(): array
    {
        return [
            'litros' => 'decimal:3',
            'precio' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
