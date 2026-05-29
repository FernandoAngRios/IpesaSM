<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucionItem extends Model
{
    protected $fillable = [
        'devolucion_id',
        'venta_item_id',
        'nombre_producto',
        'nombre_presentacion',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad'       => 'decimal:3',
        'precio_unitario'=> 'decimal:2',
        'subtotal'       => 'decimal:2',
    ];

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(Devolucion::class);
    }

    public function ventaItem(): BelongsTo
    {
        return $this->belongsTo(VentaItem::class);
    }
}
