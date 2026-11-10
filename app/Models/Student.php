<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    public const STUDY_PROGRAMS = [
        'Teknik Informatika S1',
        'Sistem Informasi S1',
        'Sistem Komputer S1',
        'Manajemen S1',
        'Akuntansi S1',
        'Akuntansi Perpajakan D4',
        'Ilmu Hukum S1',
        'Teknik Mesin S1',
        'Teknik Elektro S1',
        'Teknik Industri S1',
        'Teknik Kimia S1',
        'Sastra Inggris S1',
        'Sastra Indonesia S1',
        'Pendidikan Ekonomi S1',
        'Pendidikan Pancasila dan Kewarganegaraan S1',
        'Pendidikan Guru PAUD S1',
        'Matematika S1',
        'Sekretari D3',
        'Manajemen Informatika D3',
    ];

    protected $fillable = [
        'nim',
        'name',
        'study_program',
        'semester',
        'whatsapp_number',
        'is_active',
        'notes',
    ];

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

