<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    public const STATUS_BOT_ACTIVE = 'BOT_ACTIVE';
    public const STATUS_WAITING_ADMIN = 'WAITING_ADMIN';
    public const STATUS_ADMIN_ACTIVE = 'ADMIN_ACTIVE';
    public const STATUS_CLOSED = 'CLOSED';

    public const STATUS_LABELS = [
        self::STATUS_BOT_ACTIVE => 'Bot Aktif',
        self::STATUS_WAITING_ADMIN => 'Menunggu Admin',
        self::STATUS_ADMIN_ACTIVE => 'Admin Aktif',
        self::STATUS_CLOSED => 'Selesai',
    ];

    protected $fillable = [
        'channel',
        'external_user_id',
        'user_name',
        'contact_number',
        'status',
        'assigned_admin_id',
        'taken_over_at',
        'closed_at',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'taken_over_at' => 'datetime',
            'closed_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('id');
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}

