<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devolucion extends Model
{
    protected $table = 'devoluciones';

    protected $fillable = [
        'venta_id',
        'sucursal_id',
        'user_id',
        'motivo',
        'total_devuelto',
    ];

    protected $casts = [
        'total_devuelto' => 'decimal:2',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DevolucionItem::class);
    }
}
