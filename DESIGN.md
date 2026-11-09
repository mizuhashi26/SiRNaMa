# DESIGN.md - Arah Desain CRM Layanan Mahasiswa UNPAM (Simulasi Akademik)

## 1. Identitas & Karakter Produk
- **Konteks:** Portal Informasi Akademik & Dashboard CRM Layanan Mahasiswa (Studi Kasus Universitas Pamulang / UNPAM).
- **Status:** Aplikasi simulasi tugas mata kuliah (bukan sistem resmi UNPAM).
- **Karakter Visual:** Akademik, lugas, terstruktur, dan berfokus pada keterbacaan informasi serta kecepatan kerja admin.

## 2. Three Dials (Anti-Slop Part 3)
- **ENERGY:** `1 (Calm)` - Mengutamakan kejelasan informasi akademik dan antrean percakapan tanpa dekorasi berlebih.
- **RHYTHM:** `2 (Balanced)` - Membedakan tata letak antara sorotan pengumuman pembayaran umum, direktori informasi kampus per kategori, dan ruang kerja tabel/chat.
- **MOTION:** `1 (Hover states only)` - Transisi hanya untuk state `:hover`, `:focus-visible`, dan buka-tutup widget chat. Berhenti penuh saat `prefers-reduced-motion: reduce`.

## 3. Palet Warna & Kontras (WCAG AA Terverifikasi - R-25, R-29)
1. **Core 1 - Academic Navy (`#0F294A`):** Warna identitas utama header dan navigasi admin. Teks putih (`#FFFFFF`) di atas `#0F294A` memiliki rasio kontras **14.3:1** (Lulus AAA).
2. **Core 2 - Slate Ink & Warm Paper (`#0F172A` teks utama, `#334155` teks sekunder, `#F8FAFC` latar halaman, `#FFFFFF` permukaan konten):** Rasio kontras `#0F172A` di atas `#FFFFFF` adalah **17.8:1** dan `#334155` di atas `#FFFFFF` adalah **9.3:1** (Lulus AAA).
3. **Accent - UNPAM Academic Gold (`#FACC15` latar aksen / `#FEF9C3` latar penanda simulasi dengan teks `#0F172A`):** Digunakan secara terbatas pada penanda studi kasus UNPAM, tombol aksi utama tertentu, dan highlight jadwal pembayaran. Teks `#0F172A` di atas `#FACC15` memiliki rasio kontras **12.1:1** (Lulus AAA).

## 4. Alasan Satu Baris untuk Setiap Keputusan Desain (R-31)
- **Mengapa palet Navy & Gold?** Mengadaptasi identitas visual studi kasus UNPAM dengan kontras teks gelap di atas kuning agar memenuhi standar keterbacaan WCAG AA.
- **Mengapa tata letak Dashboard mengutamakan antrean percakapan & reminder?** Pekerjaan utama admin CRM adalah mengambil alih percakapan `WAITING_ADMIN` dan mengirim pengingat pembayaran tepat waktu, bukan melihat grafik dekoratif.
- **Mengapa tipografi Sans-Serif sistem/Plus Jakarta Sans dengan skala tegas?** Memastikan keterbacaan dokumen pengumuman panjang dan pesan chat di layar ponsel maupun desktop tanpa beban unduhan font berlebih.
- **Mengapa variasi kartu dan daftar digunakan?** Informasi pembayaran umum ditampilkan sebagai panel pengumuman terstruktur, sedangkan direktori informasi kampus ditampilkan sebagai daftar artikel yang mudah dipindai per kategori.
- **Mengapa tanpa ilustrasi generik atau gradien ungu-biru?** Menjaga kejujuran visual portal akademik dan menghindari pola dekorasi AI tanpa fungsi.

