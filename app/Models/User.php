<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'active',
        'can_edit_prices',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'active'             => 'boolean',
            'can_edit_prices'    => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return in_array($this->role, ['admin', 'employee']);
    }

    public function canEditPrices(): bool
    {
        return $this->isAdmin() || $this->can_edit_prices;
    }

    public function puedeOperarSucursal(int $sucursalId): bool
    {
        if ($this->isAdmin()) return true;
        return $this->sucursales->contains('id', $sucursalId);
    }

    /**
     * Devuelve las sucursales que este usuario puede operar.
     * Admin → todas las activas. Empleado → solo las asignadas.
     */
    public function sucursalesPermitidas(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->isAdmin()) {
            return Sucursal::activo()->orderBy('nombre')->get();
        }
        return $this->sucursales()->where('activo', true)->orderBy('nombre')->get();
    }

    public function sucursales(): BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class);
    }
}
