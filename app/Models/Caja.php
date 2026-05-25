<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    protected $fillable = [
        'sucursal_id', 'user_id', 'saldo_inicial', 'saldo_final',
        'estado', 'cerrado_por', 'cerrada_at',
    ];

    protected $casts = [
        'cerrada_at'    => 'datetime',
        'saldo_inicial' => 'decimal:2',
        'saldo_final'   => 'decimal:2',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class);
    }

    public function scopeAbierta($query)
    {
        return $query->where('estado', 'abierta');
    }

    public function scopeCerrada($query)
    {
        return $query->where('estado', 'cerrada');
    }

    public function totalVentas(): float
    {
        return (float) $this->movimientos()->where('tipo', 'entrada')->whereNotNull('venta_id')->sum('monto');
    }

    public function totalEntradasManuales(): float
    {
        return (float) $this->movimientos()->where('tipo', 'entrada')->whereNull('venta_id')->sum('monto');
    }

    public function totalEntradas(): float
    {
        return $this->totalVentas() + $this->totalEntradasManuales();
    }

    public function totalSalidas(): float
    {
        return (float) $this->movimientos()->where('tipo', 'salida')->sum('monto');
    }

    public function saldoActual(): float
    {
        return (float) $this->saldo_inicial + $this->totalEntradas() - $this->totalSalidas();
    }
}
