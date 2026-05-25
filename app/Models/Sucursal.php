<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = ['nombre', 'direccion', 'telefono', 'foto', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function inventario(): HasMany
    {
        return $this->hasMany(SucursalProducto::class);
    }

    public function transferenciasOrigen(): HasMany
    {
        return $this->hasMany(Transferencia::class, 'origen_id');
    }

    public function transferenciasDestino(): HasMany
    {
        return $this->hasMany(Transferencia::class, 'destino_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
