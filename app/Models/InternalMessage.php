<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalMessage extends Model
{
    protected $fillable = ['sender_id', 'recipient_id', 'subject', 'body', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isBroadcast(): bool
    {
        return $this->recipient_id === null;
    }

    // Mensajes recibidos en el buzón del admin (enviados por empleados)
    public function scopeForAdminInbox(Builder $query): Builder
    {
        return $query->whereHas('sender', fn ($q) => $q->where('role', 'employee'));
    }

    // Mensajes que un empleado puede ver: directos a él o broadcast del admin
    public function scopeForEmployeeInbox(Builder $query, int $userId): Builder
    {
        return $query->whereHas('sender', fn ($q) => $q->where('role', 'admin'))
            ->where(fn ($q) => $q
                ->where('recipient_id', $userId)
                ->orWhereNull('recipient_id')
            );
    }
}
