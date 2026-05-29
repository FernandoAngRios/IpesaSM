<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $fillable = [
        'sucursal_id',
        'user_id',
        'estado',
        'cliente_nombre',
        'cliente_telefono',
        'vendedor',
        'total',
        'descuento',
    ];

    protected $casts = [
        'total'     => 'decimal:2',
        'descuento' => 'decimal:2',
    ];

    public function subtotalItems(): float
    {
        return (float) $this->items()->sum('subtotal');
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
        return $this->hasMany(VentaItem::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(VentaPago::class);
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(Devolucion::class);
    }

    public function totalDevuelto(): float
    {
        return (float) $this->devoluciones()->sum('total_devuelto');
    }

    public function totalPagado(): float
    {
        return (float) $this->pagos->sum('monto');
    }

    public function cambio(): float
    {
        return max(0, $this->totalPagado() - (float) $this->total);
    }

    public function scopeAbierta($query)
    {
        return $query->where('estado', 'abierta');
    }

    public function scopeCerrada($query)
    {
        return $query->where('estado', 'cerrada');
    }
}
