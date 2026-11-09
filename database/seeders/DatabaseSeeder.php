<?php

namespace Database\Seeders;

use App\Models\CampusInformation;
use App\Models\PaymentReminder;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin Default
        User::updateOrCreate(
            ['email' => 'admin@unpam.ac.id'],
            [
                'name' => 'Admin Layanan UNPAM',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Data Mahasiswa
        $students = [
            [
                'nim' => '2110114001',
                'name' => 'Rizky Pratama',
                'study_program' => 'Teknik Informatika S1',
                'semester' => 5,
                'whatsapp_number' => '6281200000001',
                'is_active' => true,
                'notes' => 'Kontak pengujian WhatsApp #1',
            ],
            [
                'nim' => '2110114002',
                'name' => 'Siti Nurhaliza Putri',
                'study_program' => 'Sistem Informasi S1',
                'semester' => 5,
                'whatsapp_number' => '6281200000002',
                'is_active' => true,
                'notes' => 'Kontak pengujian WhatsApp #2',
            ],
            [
                'nim' => '2210112015',
                'name' => 'Dimas Aditya Saputra',
                'study_program' => 'Manajemen S1',
                'semester' => 3,
                'whatsapp_number' => '6281200000003',
                'is_active' => true,
                'notes' => 'Kontak pengujian WhatsApp #3',
            ],
            [
                'nim' => '2210113008',
                'name' => 'Nadia Maharani',
                'study_program' => 'Akuntansi S1',
                'semester' => 3,
                'whatsapp_number' => '6281200000004',
                'is_active' => true,
                'notes' => 'Kontak pengujian WhatsApp #4',
            ],
            [
                'nim' => '2310114042',
                'name' => 'Bagas Kurniawan',
                'study_program' => 'Teknik Informatika S1',
                'semester' => 1,
                'whatsapp_number' => '6281200000005',
                'is_active' => true,
                'notes' => 'Kontak pengujian WhatsApp #5',
            ],
        ];

        foreach ($students as $studentData) {
            Student::updateOrCreate(
                ['nim' => $studentData['nim']],
                $studentData
            );
        }

        // 3. Sumber Data Informasi Kampus Terpadu (Website, Chatbot Web, & Chatbot WhatsApp)
        $informations = [
            [
                'slug' => 'jadwal-pembayaran-ukt-semester-berjalan',
                'title' => 'Jadwal & Ketentuan Pembayaran Kuliah Semester Berjalan',
                'category' => 'pembayaran',
                'summary' => 'Informasi jadwal pembayaran kuliah (registrasi ulang dan angsuran) mahasiswa Universitas Pamulang.',
                'content' => "Berikut adalah rincian jadwal pembayaran kuliah pada semester berjalan:\n\n"
                    . "1. Periode Pembayaran Registrasi Awal: Tanggal 1 s/d 15 setiap awal semester.\n"
                    . "2. Periode Pembayaran Angsuran Bulanan / UTS: Tanggal 1 s/d 10 sebelum pelaksanaan ujian.\n"
                    . "3. Periode Pembayaran Angsuran UAS: Tanggal 1 s/d 10 sebelum pelaksanaan Ujian Akhir Semester.\n\n"
                    . "Catatan Layanan:\n"
                    . "- Pastikan melakukan pembayaran sesuai rentang waktu yang telah ditetapkan.\n"
                    . "- Apabila Anda memiliki kendala administrasi pembayaran, silakan pilih menu 3 (Hubungi Admin) pada layanan chat.",
                'is_published' => true,
                'is_pinned' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'slug' => 'panduan-saluran-pembayaran-kuliah',
                'title' => 'Panduan Saluran Pembayaran & Konfirmasi Administrasi',
                'category' => 'pembayaran',
                'summary' => 'Petunjuk kanal pembayaran kuliah mahasiswa dan prosedur verifikasi bukti transaksi.',
                'content' => "Panduan pembayaran biaya pendidikan Universitas Pamulang:\n\n"
                    . "1. Pembayaran dilakukan melalui nomor Virtual Account (VA) Bank yang tertera pada slip tagihan.\n"
                    . "2. Simpan bukti transaksi pembayaran Anda sebagai arsip pribadi.\n"
                    . "3. Jika membutuhkan konfirmasi atau kendala transaksi, hubungi Admin Layanan melalui menu 3 pada Chatbot.",
                'is_published' => true,
                'is_pinned' => false,
                'published_at' => now()->subDay(),
            ],
            [
                'slug' => 'prosedur-pengisian-krs-dan-perwalian',
                'title' => 'Prosedur Pengisian KRS dan Konsultasi Akademik',
                'category' => 'akademik',
                'summary' => 'Tahapan pengisian Kartu Rencana Studi (KRS) dan konsultasi dosen pembimbing akademik.',
                'content' => "Mahasiswa diharapkan memperhatikan jadwal pengisian KRS pada awal semester:\n\n"
                    . "1. Periksa daftar mata kuliah yang ditawarkan sesuai kurikulum program studi.\n"
                    . "2. Lakukan konsultasi rencana studi bersama dosen pembimbing akademik.\n"
                    . "3. Pastikan cetak dan simpan bukti KRS yang telah disetujui.",
                'is_published' => true,
                'is_pinned' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'slug' => 'kalender-akademik-semester-ganjil',
                'title' => 'Kalender Akademik Semester Berjalan',
                'category' => 'kalender_akademik',
                'summary' => 'Rangkaian agenda akademik mulai dari masa perkuliahan, evaluasi tengah semester, hingga ujian akhir.',
                'content' => "Ringkasan Kalender Akademik Universitas Pamulang:\n\n"
                    . "- Minggu 1 s/d 7: Masa Perkuliahan Tatap Muka & Daring Tahap I\n"
                    . "- Minggu 8: Pelaksanaan Ujian Tengah Semester (UTS)\n"
                    . "- Minggu 9 s/d 15: Masa Perkuliahan Tahap II\n"
                    . "- Minggu 16: Pelaksanaan Ujian Akhir Semester (UAS)",
                'is_published' => true,
                'is_pinned' => false,
                'published_at' => now()->subDays(4),
            ],
            [
                'slug' => 'jadwal-dan-tata-tertib-ujian-semester',
                'title' => 'Jadwal dan Tata Tertib Ujian Semester',
                'category' => 'jadwal_ujian',
                'summary' => 'Ketentuan kehadiran, kelengkapan kartu ujian, dan jadwal pelaksanaan ujian mahasiswa.',
                'content' => "Tata Tertib Pelaksanaan Ujian:\n\n"
                    . "1. Mahasiswa wajib membawa Kartu Tanda Mahasiswa (KTM) dan Kartu Peserta Ujian.\n"
                    . "2. Hadir di ruang ujian minimal 15 menit sebelum jadwal dimulai.\n"
                    . "3. Mengenakan pakaian kemeja putih dan celana/rok gelap sesuai ketentuan kampus.",
                'is_published' => true,
                'is_pinned' => false,
                'published_at' => now()->subDays(2),
            ],
            [
                'slug' => 'pengumuman-layanan-terpadu-mahasiswa',
                'title' => 'Pengumuman Layanan Informasi & Live Chat Mahasiswa',
                'category' => 'pengumuman',
                'summary' => 'Informasi jam operasional layanan admin melalui kanal Website dan WhatsApp.',
                'content' => "Layanan informasi otomatis melalui Chatbot Website dan WhatsApp aktif 24 jam.\n\n"
                    . "Untuk layanan konsultasi langsung bersama Admin (menu 3. Hubungi Admin), jam operasional adalah:\n"
                    . "- Senin s/d Jumat: 08.00 - 16.00 WIB\n"
                    . "- Sabtu: 08.00 - 13.00 WIB",
                'is_published' => true,
                'is_pinned' => true,
                'published_at' => now()->subHours(12),
            ],
            [
                'slug' => 'seminar-nasional-teknologi-informasi-mahasiswa',
                'title' => 'Kegiatan Seminar & Workshop Pengembangan Kompetensi Mahasiswa',
                'category' => 'kegiatan',
                'summary' => 'Agenda kegiatan kemahasiswaan dan seminar akademik semester berjalan.',
                'content' => "Fakultas menyelenggarakan kegiatan workshop pengembangan perangkat lunak dan manajemen proyek bagi mahasiswa aktif.\n\n"
                    . "Informasi pendaftaran dan jadwal lengkap dapat diperoleh melalui koordinator program studi.",
                'is_published' => true,
                'is_pinned' => false,
                'published_at' => now()->subDays(5),
            ],
            [
                'slug' => 'layanan-surat-keterangan-aktif-kuliah',
                'title' => 'Prosedur Pengajuan Surat Keterangan Aktif Kuliah',
                'category' => 'administrasi',
                'summary' => 'Syarat dan alur pengajuan surat keterangan aktif kuliah pada bagian administrasi layanan.',
                'content' => "Persyaratan pengajuan Surat Keterangan Aktif Kuliah:\n\n"
                    . "1. Terdaftar sebagai mahasiswa aktif pada semester berjalan.\n"
                    . "2. Melampirkan data NIM, Nama Lengkap, Program Studi, dan keperluan surat.\n"
                    . "3. Pengajuan diproses dalam waktu 1-2 hari kerja.",
                'is_published' => true,
                'is_pinned' => false,
                'published_at' => now()->subDays(3),
            ],
        ];

        foreach ($informations as $infoData) {
            CampusInformation::updateOrCreate(
                ['slug' => $infoData['slug']],
                $infoData
            );
        }

        // 4. Jadwal Reminder Pembayaran
        PaymentReminder::updateOrCreate(
            ['title' => 'Pengingat Pembayaran Angsuran Kuliah & Registrasi Ujian'],
            [
                'message' => 'Mengingatkan kepada seluruh mahasiswa bahwa batas waktu pembayaran angsuran kuliah sebelum pelaksanaan ujian semester jatuh pada tanggal 10 bulan berjalan. Mohon segera menyelesaikan administrasi sebelum batas waktu.',
                'scheduled_at' => now()->addDays(2)->setTime(9, 0),
                'status' => 'scheduled',
                'total_targets' => Student::where('is_active', true)->count(),
            ]
        );
    }
}
