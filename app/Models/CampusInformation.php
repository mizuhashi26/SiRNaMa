<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampusInformation extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'akademik' => 'Akademik',
        'kalender_akademik' => 'Kalender Akademik',
        'jadwal_ujian' => 'Jadwal Perkuliahan & Ujian',
        'pengumuman' => 'Pengumuman Kampus',
        'kegiatan' => 'Kegiatan & Agenda',
        'administrasi' => 'Administrasi Layanan',
        'pembayaran' => 'Informasi Pembayaran Kuliah',
    ];

    protected $table = 'campus_informations';

    protected $fillable = [
        'title',
        'slug',
        'category',
        'summary',
        'content',
        'is_published',
        'is_pinned',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function (Builder $sub) {
                $sub->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }
}

