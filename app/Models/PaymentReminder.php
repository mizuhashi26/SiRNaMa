<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'scheduled_at',
        'status',
        'sent_at',
        'total_targets',
        'sent_count',
        'failed_count',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'total_targets' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentReminderLog::class);
    }
}

