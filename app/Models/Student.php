<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    public const STUDY_PROGRAMS = [
        'Sistem Informasi S1',
        'Teknik Informatika S1',
        'Sistem Komputer S1',
        'Manajemen S1',
        'Akuntansi S1',
        'Ilmu Hukum S1',
        'Teknik Mesin S1',
        'Teknik Elektro S1',
        'Teknik Industri S1',
        'Teknik Kimia S1',
        'Ilmu Komunikasi S1',
        'Desain Komunikasi Visual S1',
        'Pendidikan Pancasila dan Kewarganegaraan S1',
        'Pendidikan Ekonomi S1',
        'Pendidikan Guru Sekolah Dasar S1',
        'Pendidikan Jasmani S1',
        'Sastra Indonesia S1',
        'Sastra Inggris S1',
        'Ekonomi Syariah S1',
        'Manajemen Pendidikan Islam S1',
        'Matematika S1',
        'Biologi S1',
        'Kimia S1',
        'Administrasi Negara S1',
        'Ilmu Pemerintahan S1',
        'Administrasi Perkantoran D3',
        'Akuntansi D3',
        'Akuntansi Perpajakan D4',
        'Magister Manajemen S2',
        'Magister Ilmu Hukum S2',
        'Magister Akuntansi S2',
        'Magister Teknik Informatika S2',
        'Magister Manajemen Pendidikan S2',
    ];

    public const REGULAR_CLASSES = [
        'reg_a' => 'Reguler A (Pagi)',
        'reg_b' => 'Reguler B (Malam)',
        'reg_ck' => 'Reguler CK (Kamis)',
        'reg_cs' => 'Reguler CS (Sabtu)',
    ];

    protected $fillable = [
        'nim',
        'name',
        'study_program',
        'regular_class',
        'semester',
        'whatsapp_number',
        'is_active',
        'notes',
    ];

    public function getRegularClassLabelAttribute(): string
    {
        return self::REGULAR_CLASSES[$this->regular_class] ?? self::REGULAR_CLASSES['reg_a'];
    }

    protected function casts(): array
    {
        return [
            'semester' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '08')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }
}

