<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transferencia extends Model
{
    protected $fillable = [
        'origen_id', 'destino_id', 'product_id',
        'cantidad_litros', 'nota', 'user_id',
        'estado', 'cantidad_recibida', 'confirmado_por', 'confirmado_at', 'nota_recepcion',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_litros'   => 'decimal:3',
            'cantidad_recibida' => 'decimal:3',
            'confirmado_at'     => 'datetime',
        ];
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'origen_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'destino_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }

    public function pendiente(): bool
    {
        return $this->estado === 'pendiente';
    }
}
