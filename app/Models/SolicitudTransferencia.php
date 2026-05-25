<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolicitudTransferencia extends Model
{
    protected $fillable = [
        'solicitante_sucursal_id',
        'origen_sucursal_id',
        'user_id',
        'estado',
        'notas_solicitud',
        'notas_envio',
        'notas_recepcion',
        'procesado_por',
        'procesado_at',
        'recibido_por',
        'recibido_at',
    ];

    protected function casts(): array
    {
        return [
            'procesado_at' => 'datetime',
            'recibido_at'  => 'datetime',
        ];
    }

    public function solicitanteSucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'solicitante_sucursal_id');
    }

    public function origenSucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'origen_sucursal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function procesadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por');
    }

    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SolicitudTransferenciaItem::class, 'solicitud_id');
    }

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnviada($query)
    {
        return $query->where('estado', 'enviada');
    }

    public function scopeRecibida($query)
    {
        return $query->where('estado', 'recibida');
    }

    public function esPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function esEnviada(): bool
    {
        return $this->estado === 'enviada';
    }

    public function esRecibida(): bool
    {
        return $this->estado === 'recibida';
    }

    public function esCancelada(): bool
    {
        return $this->estado === 'cancelada';
    }
}
