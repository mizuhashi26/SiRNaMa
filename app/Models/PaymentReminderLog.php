<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_reminder_id',
        'student_id',
        'recipient_name',
        'whatsapp_number',
        'status',
        'provider_response',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(PaymentReminder::class, 'payment_reminder_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

