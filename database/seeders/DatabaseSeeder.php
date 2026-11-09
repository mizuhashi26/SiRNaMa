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
            ['email' => 'admin@unpam-simulasi.ac.id'],
            [
                'name' => 'Admin Layanan UNPAM',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Data Mahasiswa Dummy (Simulasi)
        $dummyStudents = [
            [
                'nim' => '2110114001',
                'name' => 'Rizky Pratama (Simulasi)',
                'study_program' => 'Teknik Informatika S1',
                'semester' => 5,
                'whatsapp_number' => '6281200000001',
                'is_active' => true,
                'notes' => 'Data dummy mahasiswa pengujian #1',
            ],
            [
                'nim' => '2110114002',
                'name' => 'Siti Nurhaliza Putri (Simulasi)',
                'study_program' => 'Sistem Informasi S1',
                'semester' => 5,
                'whatsapp_number' => '6281200000002',
                'is_active' => true,
                'notes' => 'Data dummy mahasiswa pengujian #2',
            ],
            [
                'nim' => '2210112015',
                'name' => 'Dimas Aditya Saputra (Simulasi)',
                'study_program' => 'Manajemen S1',
                'semester' => 3,
                'whatsapp_number' => '6281200000003',
                'is_active' => true,
                'notes' => 'Data dummy mahasiswa pengujian #3',
            ],
            [
                'nim' => '2210113008',
                'name' => 'Nadia Maharani (Simulasi)',
                'study_program' => 'Akuntansi S1',
                'semester' => 3,
                'whatsapp_number' => '6281200000004',
                'is_active' => true,
                'notes' => 'Data dummy mahasiswa pengujian #4',
            ],
            [
                'nim' => '2310114042',
                'name' => 'Bagas Kurniawan (Simulasi)',
                'study_program' => 'Teknik Informatika S1',
                'semester' => 1,
                'whatsapp_number' => '6281200000005',
                'is_active' => true,
                'notes' => 'Data dummy mahasiswa pengujian #5',
            ],
        ];

        foreach ($dummyStudents as $studentData) {
            Student::updateOrCreate(
                ['nim' => $studentData['nim']],
                $studentData
            );
        }

        // 3. Sumber Data Informasi Kampus Terpadu (Website, Chatbot Web, & Chatbot WhatsApp)
        $informations = [
            [
                'slug' => 'jadwal-pembayaran-ukt-semester-berjalan-simulasi',
                'title' => 'Jadwal & Ketentuan Pembayaran Kuliah Umum Semester Berjalan (Simulasi)',
                'category' => 'pembayaran',
                'summary' => 'Informasi jadwal pembayaran kuliah umum (registrasi ulang dan angsuran) untuk simulasi layanan CRM mahasiswa.',
                'content' => "[DATA SIMULASI AKADEMIK - BUKAN PENGUMUMAN RESMI UNPAM]\n\n"
                    . "Berikut adalah rincian jadwal pembayaran kuliah umum pada simulasi semester berjalan:\n"
                    . "1. Periode Pembayaran Registrasi Awal: Tanggal 1 s/d 15 setiap awal semester.\n"
                    . "2. Periode Pembayaran Angsuran Bulanan / UTS: Tanggal 1 s/d 10 sebelum pelaksanaan ujian.\n"
                    . "3. Periode Pembayaran Angsuran UAS: Tanggal 1 s/d 10 sebelum pelaksanaan Ujian Akhir Semester.\n\n"
                    . "Catatan Layanan:\n"
                    . "- Informasi ini berlaku sebagai pengingat jadwal pembayaran umum bagi seluruh mahasiswa.\n"
                    . "- Apabila Anda memiliki kendala administrasi pembayaran, silakan pilih menu 3 (Hubungi Admin) pada layanan chat.",
                'is_published' => true,
                'is_pinned' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'slug' => 'panduan-saluran-pembayaran-kuliah-simulasi',
                'title' => 'Panduan Saluran Pembayaran & Konfirmasi Administrasi (Simulasi)',
                'category' => 'pembayaran',
                'summary' => 'Petunjuk simulasi kanal pembayaran umum mahasiswa dan prosedur bantuan apabila terjadi keterlambatan.',
                'content' => "[DATA SIMULASI AKADEMIK]\n\n"
                    . "Panduan umum pembayaran biaya pendidikan pada skenario simulasi CRM:\n"
                    . "1. Pastikan melakukan pembayaran sesuai rentang tanggal kalender akademik yang diumumkan.\n"
                    . "2. Simpan bukti transaksi pembayaran Anda sebagai arsip pribadi.\n"
                    . "3. Jika membutuhkan konsultasi jadwal pembayaran umum, hubungi Admin Layanan melalui menu 3 pada Chatbot.",
                'is_published' => true,
                'is_pinned' => false,
                'published_at' => now()->subDay(),
            ],
            [
                'slug' => 'prosedur-pengisian-krs-dan-perwalian-simulasi',
                'title' => 'Prosedur Pengisian KRS dan Konsultasi Akademik (Simulasi)',
                'category' => 'akademik',
                'summary' => 'Tahapan pengisian Kartu Rencana Studi (KRS) dan konsultasi dosen wali pada simulasi layanan akademik.',
                'content' => "[DATA SIMULASI AKADEMIK]\n\n"
                    . "Mahasiswa diharapkan memperhatikan jadwal pengisian KRS pada awal semester:\n"
                    . "1. Periksa daftar mata kuliah yang ditawarkan sesuai kurikulum program studi.\n"
                    . "2. Lakukan konsultasi rencana studi bersama dosen pembimbing akademik.\n"
                    . "3. Pastikan cetak dan simpan bukti KRS yang telah disetujui.",
                'is_published' => true,
                'is_pinned' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'slug' => 'kalender-akademik-semester-ganjil-simulasi',
                'title' => 'Kalender Akademik Semester Berjalan (Simulasi)',
                'category' => 'kalender_akademik',
                'summary' => 'Rangkaian agenda akademik mulai dari masa perkuliahan, evaluasi tengah semester, hingga ujian akhir.',
                'content' => "[DATA SIMULASI AKADEMIK]\n\n"
                    . "Ringkasan Kalender Akademik (Data Contoh Demonstrasi):\n"
                    . "- Minggu 1 s/d 7: Masa Perkuliahan Tatap Muka & Daring Tahap I\n"
                    . "- Minggu 8: Pelaksanaan Ujian Tengah Semester (UTS)\n"
                    . "- Minggu 9 s/d 15: Masa Perkuliahan Tahap II\n"
                    . "- Minggu 16: Pelaksanaan Ujian Akhir Semester (UAS)",
                'is_published' => true,
                'is_pinned' => false,
                'published_at' => now()->subDays(4),
            ],
            [
                'slug' => 'jadwal-dan-tata-tertib-ujian-semester-simulasi',
                'title' => 'Jadwal dan Tata Tertib Ujian Semester (Simulasi)',
                'category' => 'jadwal_ujian',
                'summary' => 'Ketentuan kehadiran, kelengkapan kartu ujian, dan jadwal pelaksanaan ujian mahasiswa.',
                'content' => "[DATA SIMULASI AKADEMIK]\n\n"
                    . "Tata Tertib Pelaksanaan Ujian (Simulasi):\n"
                    . "1. Mahasiswa wajib membawa Kartu Tanda Mahasiswa (KTM) dan Kartu Peserta Ujian.\n"
                    . "2. Hadir di ruang ujian minimal 15 menit sebelum jadwal dimulai.\n"
                    . "3. Mengenakan pakaian rapi sesuai ketentuan akademik kampus.",
                'is_published' => true,
                'is_pinned' => false,
                'published_at' => now()->subDays(2),
            ],
            [
                'slug' => 'pengumuman-layanan-terpadu-crm-mahasiswa-simulasi',
                'title' => 'Pengumuman Layanan Informasi & Live Chat Mahasiswa (Simulasi)',
                'category' => 'pengumuman',
                'summary' => 'Informasi jam operasional layanan admin CRM melalui kanal Website dan WhatsApp.',
                'content' => "[DATA SIMULASI AKADEMIK]\n\n"
                    . "Layanan informasi otomatis melalui Chatbot Website dan WhatsApp dapat diakses 24 jam.\n"
                    . "Untuk layanan konsultasi langsung bersama Admin (menu 3. Hubungi Admin), jam operasional simulasi adalah:\n"
                    . "- Senin s/d Jumat: 08.00 - 16.00 WIB\n"
                    . "- Sabtu: 08.00 - 13.00 WIB",
                'is_published' => true,
                'is_pinned' => true,
                'published_at' => now()->subHours(12),
            ],
            [
                'slug' => 'seminar-nasional-teknologi-informasi-mahasiswa-simulasi',
                'title' => 'Kegiatan Seminar & Workshop Pengembangan Kompetensi Mahasiswa (Simulasi)',
                'category' => 'kegiatan',
                'summary' => 'Agenda kegiatan kemahasiswaan dan seminar akademik semester ini (data simulasi).',
                'content' => "[DATA SIMULASI AKADEMIK]\n\n"
                    . "Program studi menyelenggarakan kegiatan workshop pengembangan perangkat lunak dan manajemen proyek bagi mahasiswa aktif.\n"
                    . "Pendaftaran simulasi dilakukan melalui portal kegiatan kemahasiswaan.",
                'is_published' => true,
                'is_pinned' => false,
                'published_at' => now()->subDays(5),
            ],
            [
                'slug' => 'layanan-surat-keterangan-aktif-kuliah-simulasi',
                'title' => 'Prosedur Pengajuan Surat Keterangan Aktif Kuliah (Simulasi)',
                'category' => 'administrasi',
                'summary' => 'Syarat dan alur pengajuan surat keterangan aktif kuliah pada bagian administrasi layanan mahasiswa.',
                'content' => "[DATA SIMULASI AKADEMIK]\n\n"
                    . "Persyaratan pengajuan Surat Keterangan Aktif Kuliah (Simulasi):\n"
                    . "1. Terdaftar sebagai mahasiswa aktif pada semester berjalan.\n"
                    . "2. Melampirkan data NIM, Nama Lengkap, Program Studi, dan keperluan surat.\n"
                    . "3. Jika membutuhkan bantuan verifikasi berkas, pilih menu 3 (Hubungi Admin) pada Chatbot.",
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

        // 4. Contoh Jadwal Reminder Pembayaran Umum
        PaymentReminder::updateOrCreate(
            ['title' => 'Reminder Pembayaran Angsuran Kuliah & Registrasi Ujian (Simulasi)'],
            [
                'message' => 'Mengingatkan kepada seluruh mahasiswa bahwa batas waktu pembayaran kuliah umum sebelum pelaksanaan ujian semester jatuh pada tanggal 10 bulan berjalan. Mohon abaikan pesan ini apabila Anda telah menyelesaikan administrasi pembayaran.',
                'scheduled_at' => now()->addDays(2)->setTime(9, 0),
                'status' => 'scheduled',
                'total_targets' => Student::where('is_active', true)->count(),
            ]
        );
    }
}
