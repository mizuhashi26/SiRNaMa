<?php

namespace Database\Seeders;

use App\Models\CampusInformation;
use Illuminate\Database\Seeder;

class KtmInformationSeeder extends Seeder
{
    public const SLUG = 'alur-pembuatan-ulang-ktm-hilang';

    /**
     * Definisi artikel panduan pembuatan ulang KTM yang hilang.
     *
     * @return array<string, mixed>
     */
    public static function articleData(): array
    {
        return [
            'slug' => self::SLUG,
            'title' => 'Alur dan Syarat Pembuatan KTM Baru & Cetak Ulang KTM Hilang',
            'category' => 'administrasi',
            'summary' => 'Panduan lengkap syarat pembuatan KTM baru biasa (tanpa surat kehilangan) dan alur cetak ulang KTM Universitas Pamulang yang hilang.',
            'content' => "Halo Kak! Berikut alur dan syarat pengurusan Kartu Tanda Mahasiswa (KTM) Universitas Pamulang:\n\n"
                . "*Catatan Pembuatan KTM Baru Biasa (Bukan Hilang):*\n"
                . "- Pembuatan KTM baru biasa dapat diproses *tanpa Surat Keterangan Kehilangan Kepolisian* (cukup lengkapi SKM bertanda tangan Prodi, validasi Surat Pembuatan E-Student Card, serta pengajuan ke IT Support sesuai ketentuan yang berlaku).\n\n"
                . "*Alur Lengkap Pembuatan Ulang KTM Hilang:*\n"
                . "1. Unduh Dokumen di MyUnpam\n"
                . "- Masuk ke MyUnpam pada bagian Dokumen, lalu unduh Surat Keterangan Mahasiswa (SKM) dan Surat Keterangan Pembuatan E-Student Card.\n\n"
                . "2. Surat Kehilangan Kepolisian (Khusus KTM Hilang)\n"
                . "- Minta tanda tangan SKM ke pihak Program Studi (Prodi).\n"
                . "- Untuk KTM hilang, bawa SKM yang sudah ditandatangani ke kantor kepolisian (bisa di Polsek Pamulang) untuk membuat Surat Keterangan Kehilangan KTM. (Tahap surat kepolisian ini tidak diperlukan untuk pembuatan KTM baru biasa).\n\n"
                . "3. Validasi Surat Pembuatan E-Student Card\n"
                . "- Minta tanda tangan Kepala Perpustakaan di Front Office Perpustakaan (UNPAM Viktor, Gedung C Lt. 10).\n"
                . "- Minta tanda tangan Wakil Rektor III di Ruang LKA (sebelah perpustakaan).\n\n"
                . "4. Pembayaran Denda (Khusus KTM Hilang)\n"
                . "- Bawa Surat Kehilangan KTM dan Surat Pembuatan E-Student Card yang sudah divalidasi ke BPR (Gedung B Lt. 1) untuk membayar denda sebesar Rp100.000 (tunai).\n\n"
                . "5. Pengajuan Cetak Kartu\n"
                . "- Serahkan seluruh berkas (beserta kwitansi pembayaran bagi pengurusan KTM hilang) ke bagian IT Support di:\n"
                . "  - Kampus Viktor (Gedung A Lt. 4), atau\n"
                . "  - Kampus Pusat (Gedung A Lt. 2 Ruang 210).\n\n"
                . "Silakan diproses secara berurutan ya Kak. Jika ada kendala di salah satu tahap, silakan hubungi kembali Admin melalui menu 3 (Hubungi Admin).",
            'is_published' => true,
            'is_pinned' => true,
            'published_at' => now(),
        ];
    }

    public function run(): void
    {
        $data = self::articleData();

        CampusInformation::updateOrCreate(
            ['slug' => $data['slug']],
            $data
        );
    }
}
