<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoTinta extends Model
{
    protected $table = 'movimientos_tinta';

    protected $fillable = [
        'tinta_id', 'usuario_id', 'tipo', 'cantidad_litros', 'referencia',
    ];

    protected function casts(): array
    {
        return ['cantidad_litros' => 'decimal:3'];
    }

    public function tinta(): BelongsTo
    {
        return $this->belongsTo(Tinta::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
