<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentReminder extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'pembayaran' => 'Pembayaran & Keuangan',
        'akademik' => 'Akademik & KRS',
        'ujian' => 'Jadwal Ujian (UTS/UAS)',
        'pengumuman' => 'Pengumuman & Beasiswa',
        'administrasi' => 'Administrasi & Berkas',
        'kegiatan' => 'Kegiatan & Seminar',
        'umum' => 'Pengingat Umum',
    ];

    public const TARGET_TYPES = [
        'all' => 'Semua Mahasiswa Aktif',
        'personal' => 'Personal (1 Penerima)',
        'selected' => 'Beberapa Mahasiswa / Nomor Pilihan',
    ];

    protected $fillable = [
        'title',
        'category',
        'target_type',
        'target_student_ids',
        'custom_recipients',
        'message',
        'scheduled_at',
        'status',
        'sent_at',
        'total_targets',
        'sent_count',
        'failed_count',
        'last_error',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Pengingat Mahasiswa';
    }

    public function getTargetTypeLabelAttribute(): string
    {
        return self::TARGET_TYPES[$this->target_type] ?? self::TARGET_TYPES['all'];
    }

    protected function casts(): array
    {
        return [
            'target_student_ids' => 'array',
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

