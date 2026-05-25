<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudTransferenciaItem extends Model
{
    protected $fillable = [
        'solicitud_id',
        'product_id',
        'cantidad_solicitada',
        'cantidad_enviada',
        'cantidad_recibida',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_solicitada' => 'decimal:3',
            'cantidad_enviada'    => 'decimal:3',
            'cantidad_recibida'   => 'decimal:3',
        ];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudTransferencia::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
