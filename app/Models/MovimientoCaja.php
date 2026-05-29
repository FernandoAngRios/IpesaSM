<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoCaja extends Model
{
    protected $table = 'movimientos_caja';

    protected $fillable = [
        'caja_id', 'user_id', 'venta_id', 'devolucion_id', 'tipo', 'concepto', 'monto',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(Devolucion::class);
    }
}
