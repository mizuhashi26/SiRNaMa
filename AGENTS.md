# AGENTS.md --- CRM Layanan Mahasiswa UNPAM

Dokumen ini adalah aturan kerja utama untuk AI coding agent di workspace
proyek CRM Layanan Mahasiswa UNPAM. Baca dan patuhi sebelum melakukan
perubahan kode.

## 1. Konteks Proyek

Nama proyek: **CRM Pengelolaan Informasi, Reminder, dan Layanan
Mahasiswa Terintegrasi WhatsApp**

Studi kasus: Universitas Pamulang (UNPAM).

Tujuan: membangun aplikasi CRM mandiri untuk tugas mata kuliah. Aplikasi
membantu admin mengelola informasi kampus, mengirim reminder pembayaran
umum, dan menangani layanan mahasiswa melalui website serta WhatsApp.

### Batasan penting

-   Aplikasi berdiri sendiri (*standalone*), bukan sistem resmi UNPAM.
-   Tidak terhubung ke database akademik, keuangan, SIAKAD, atau sistem
    internal kampus.
-   Gunakan data dummy untuk mahasiswa, nomor WhatsApp, informasi, dan
    jadwal pembayaran.
-   Jangan mengarang atau menampilkan informasi simulasi seolah-olah
    pengumuman resmi UNPAM. Beri penanda simulasi pada konten demo bila
    diperlukan.
-   Pengembangan dan pengujian dilakukan lokal melalui Laragon.
-   Belum ada kebutuhan hosting atau deployment produksi.
-   Target awal adalah MVP yang dapat didemonstrasikan dalam waktu
    pengerjaan sekitar empat hari.
-   Prioritaskan fitur yang benar-benar berjalan daripada banyak fitur
    yang belum selesai.

## 2. Pengguna dan Kanal

Hanya ada dua jenis pengguna untuk versi awal:

1.  **Admin** --- login ke dashboard untuk mengelola data dan menangani
    percakapan.
2.  **Mahasiswa/pengunjung** --- menggunakan website publik, chatbot
    website, atau WhatsApp. Mahasiswa tidak perlu login ke website untuk
    MVP.

Kanal layanan:

-   Website publik: menampilkan informasi kampus dan menyediakan chatbot
    berbasis menu.
-   WhatsApp: chatbot berbasis menu dan reminder melalui Fonnte.
-   Dashboard admin: satu tempat untuk mengelola informasi, reminder,
    dan percakapan dari kanal yang didukung.

## 3. Modul dan Ruang Lingkup MVP

### A. Pengelolaan informasi kampus

Admin mengelola informasi melalui dashboard. Informasi yang
dipublikasikan menjadi sumber data yang sama untuk:

-   Halaman informasi website.
-   Chatbot website.
-   Chatbot WhatsApp.

Contoh kategori: akademik, kalender akademik, jadwal ujian, pengumuman,
kegiatan, dan administrasi.

Hindari menyimpan salinan informasi yang berbeda untuk setiap kanal.
Ambil informasi dari sumber data yang sama.

### B. Reminder pembayaran umum

Reminder berlaku untuk **tagihan/pembayaran umum seluruh mahasiswa**,
bukan tagihan individual.

Fitur yang direncanakan:

-   Admin membuat reminder dan mengisi judul, isi pesan, serta
    tanggal/waktu pengiriman.
-   Admin dapat mengirim manual melalui tombol.
-   Sistem dapat mengirim otomatis sesuai jadwal jika scheduler dan
    lingkungan pengujian sudah siap.
-   Pesan dikirim ke daftar nomor WhatsApp mahasiswa dummy yang
    terdaftar.

Di luar ruang lingkup MVP:

-   Perhitungan tagihan per mahasiswa.
-   Pengecekan status pelunasan individual.
-   Payment gateway.
-   Sinkronisasi ke sistem keuangan kampus.

### C. Chatbot website

-   Berbentuk widget/kotak chat.
-   Menggunakan menu pilihan atau tombol, bukan AI generatif atau
    pemahaman bahasa bebas.
-   Menu awal yang disarankan:
    1.  Informasi Kampus
    2.  Informasi Pembayaran
    3.  Hubungi Admin
-   Isi informasi diambil dari data yang sama dengan website dan chatbot
    WhatsApp.

### D. Chatbot WhatsApp

-   Gunakan Fonnte sebagai WhatsApp Gateway.
-   Pesan keluar menggunakan API Fonnte.
-   Pesan masuk diproses melalui webhook Fonnte.
-   Gunakan menu angka/keyword sederhana.
-   Menu awal konsisten dengan chatbot website:
    1.  Informasi Kampus
    2.  Informasi Pembayaran
    3.  Hubungi Admin
-   Jangan mengklaim integrasi berhasil sebelum menguji pengiriman dan
    penerimaan pesan sungguhan.

### E. Live chat admin terpusat

Percakapan dari website dan WhatsApp yang berhasil diintegrasikan
ditampilkan pada satu menu Live Chat di dashboard.

Status percakapan:

-   `BOT_ACTIVE` --- chatbot sedang menangani percakapan.
-   `WAITING_ADMIN` --- mahasiswa meminta admin.
-   `ADMIN_ACTIVE` --- admin sudah mengambil alih.
-   `CLOSED` --- percakapan selesai.

Alur:

1.  Mahasiswa memilih Hubungi Admin.
2.  Percakapan masuk ke dashboard dengan status Menunggu Admin.
3.  Admin menekan **Ambil Alih**.
4.  Status berubah menjadi Admin Aktif.
5.  Admin dapat membalas melalui dashboard.
6.  Admin dapat memilih untuk menangani percakapan langsung melalui
    WhatsApp jika mekanisme dan sinkronisasinya didukung serta sudah
    diuji.
7.  Admin menekan **Akhiri Percakapan**.
8.  Status menjadi Selesai; chatbot dapat melayani interaksi berikutnya
    sesuai aturan sesi.

**Penting:** Jangan berasumsi Fonnte otomatis menyinkronkan pesan yang
diketik langsung dari aplikasi WhatsApp ke dashboard. Periksa
dokumentasi dan kemampuan webhook Fonnte yang benar-benar tersedia. Jika
sinkronisasi dua arah tidak dapat dipastikan, jelaskan keterbatasannya
dan prioritaskan balasan melalui dashboard yang dikirim menggunakan API
Fonnte.

### F. Riwayat interaksi --- opsional

Jika waktu memungkinkan, simpan identitas/kontak, kanal, isi pesan,
waktu, dan status percakapan. Analitik lanjutan bukan prioritas MVP.

## 4. Prinsip Teknis

-   Mulai dengan memeriksa isi workspace dan kondisi proyek sebelum
    membuat atau mengubah file.
-   Jangan mengasumsikan framework, versi, struktur folder, atau
    dependency sebelum memeriksa proyek dan mengonfirmasi keputusan yang
    belum ditetapkan.
-   Lingkungan lokal menggunakan Laragon dan MySQL.
-   Jika proyek Laravel digunakan, ikuti versi dan pola yang benar-benar
    terpasang pada proyek; jangan melakukan upgrade framework tanpa
    persetujuan.
-   Gunakan konfigurasi `.env` untuk kredensial dan rahasia.
-   Jangan pernah menulis token Fonnte, password, API key, atau data
    sensitif ke source code, dokumentasi publik, screenshot, atau commit
    Git.
-   Sediakan `.env.example` tanpa nilai rahasia bila diperlukan.
-   Gunakan Laravel Scheduler hanya jika stack yang dipilih
    mendukungnya; dokumentasikan cara menjalankannya secara lokal.
-   Webhook Fonnte memerlukan URL publik yang dapat dijangkau dari
    internet. Untuk pengujian lokal, gunakan tunnel yang disetujui
    pengguna (misalnya Cloudflare Tunnel atau ngrok). Jangan mengklaim
    `localhost` dapat menerima webhook publik secara langsung.
-   Validasi input di server. Jangan mengandalkan validasi frontend
    saja.
-   Terapkan autentikasi dan otorisasi untuk seluruh halaman admin dan
    endpoint pengelolaan.
-   Escape output pengguna untuk mengurangi risiko XSS.
-   Jangan menyimpan data pribadi mahasiswa sungguhan; gunakan data
    dummy.
-   Hindari dependency baru jika fitur dapat dibuat dengan dependency
    yang sudah ada.
-   Jangan menambahkan AI/LLM ke chatbot; chatbot MVP berbasis menu dan
    aturan sederhana.

## 5. Aturan Workflow untuk Agent

### Sebelum mengerjakan

1.  Baca `AGENTS.md` dan dokumen konsep proyek jika tersedia.
2.  Periksa struktur folder, `README`, file konfigurasi, dependency,
    migration, dan status Git.
3.  Identifikasi bagian yang sudah berjalan dan jangan merusaknya.
4.  Untuk tugas yang belum jelas, ajukan pertanyaan yang paling penting
    sebelum implementasi.
5.  Untuk pekerjaan lebih dari satu perubahan kecil, buat rencana
    singkat berurutan dan jelaskan file yang akan disentuh.

### Saat mengerjakan

-   Kerjakan satu modul atau satu alur pengguna dalam satu waktu.
-   Pilih perubahan terkecil yang menyelesaikan kebutuhan.
-   Pertahankan konvensi dan arsitektur proyek yang sudah ada.
-   Jangan melakukan refactor besar, mengganti UI global, mengganti
    dependency, atau mengubah skema database yang tidak terkait dengan
    tugas.
-   Jangan membuat data, endpoint, response API, atau kemampuan
    integrasi palsu untuk membuat demo terlihat berhasil.
-   Jika integrasi eksternal belum dapat diuji, tandai sebagai belum
    terverifikasi dan sediakan fallback demo yang jelas.
-   Jangan menimpa file pengguna atau menghapus data tanpa persetujuan.

### Setelah mengerjakan

1.  Jalankan pemeriksaan yang relevan: test, lint, build, migration
    check, atau pemeriksaan manual.
2.  Uji alur utama dari sisi pengguna, bukan hanya memastikan file
    berhasil dibuat.
3.  Laporkan file yang berubah, perintah yang dijalankan, hasil tes, dan
    hal yang belum terverifikasi.
4.  Jangan menyatakan fitur selesai jika pengujian gagal atau belum
    dilakukan.
5.  Jika ada error, investigasi penyebabnya sebelum membuat perubahan
    tambahan.

## 6. Pemanfaatan Plugin/Skills

Plugin yang pengguna sebut tersedia di Antigravity:

-   **Superpowers** --- gunakan untuk brainstorming kebutuhan,
    perencanaan implementasi, pemecahan tugas, TDD, review, dan
    verifikasi bila skill terkait tersedia.
-   **Caveman** --- gunakan untuk investigasi terarah, perubahan
    kecil/surgical, eksplorasi kode, dan review bila skill terkait
    tersedia.
-   **Anti-Slop** --- gunakan untuk menjaga kualitas UI, konsistensi
    visual, keterbacaan, dan menghindari hasil generik bila skill
    terkait tersedia.

Aturan penggunaan:

1.  Periksa skill/plugin yang benar-benar tersedia di lingkungan
    Antigravity.
2.  Baca instruksi `SKILL.md` atau dokumentasi plugin sebelum
    menggunakannya.
3.  Ikuti nama skill, cara pemanggilan, dan batasan yang tertulis pada
    plugin; jangan mengarang command atau menganggap semua plugin
    otomatis aktif.
4.  Jangan menjalankan semua workflow plugin untuk setiap perubahan
    kecil.
5.  Pilih skill yang paling relevan dengan tugas:
    -   Perencanaan/fitur baru: Superpowers.
    -   Investigasi bug atau perubahan terarah: Caveman.
    -   UI dan audit kualitas tampilan: Anti-Slop.
    -   Pengujian dan verifikasi: workflow testing/verifikasi yang
        tersedia, terutama Superpowers.
6.  Plugin adalah alat bantu, bukan pengganti pengujian nyata dan
    penilaian teknis.

## 7. Prinsip UI/UX

-   Gunakan identitas visual UNPAM sebagai referensi studi kasus, tetapi
    jangan mengesankan aplikasi ini sebagai layanan resmi kampus.
-   Buat tampilan modern, rapi, konsisten, dan responsif.
-   Prioritaskan keterbacaan, hierarki informasi, navigasi sederhana,
    dan status yang jelas.
-   Hindari dekorasi berlebihan, gradient acak, terlalu banyak kartu,
    animasi yang tidak perlu, dan komponen yang tidak memiliki fungsi.
-   Dashboard harus mengutamakan pekerjaan admin: informasi, reminder,
    dan percakapan.
-   Website publik harus mudah digunakan mahasiswa melalui desktop
    maupun ponsel.
-   Chat widget harus mudah ditemukan dan tidak menutupi konten utama.
-   Gunakan label bahasa Indonesia yang konsisten.

## 8. Prioritas Pengerjaan Empat Hari

Urutan prioritas yang disarankan:

1.  Fondasi aplikasi, login admin, dashboard, data mahasiswa, dan CRUD
    informasi.
2.  Halaman website dan sumber informasi bersama.
3.  Reminder umum dengan pengiriman manual melalui Fonnte.
4.  Chatbot website berbasis menu.
5.  Chatbot WhatsApp dan webhook Fonnte.
6.  Live chat dashboard: pesan masuk, Ambil Alih, balasan dari
    dashboard, dan Akhiri Percakapan.
7.  Scheduler untuk reminder otomatis dan sinkronisasi balasan WhatsApp
    langsung jika waktu serta kemampuan teknis memungkinkan.
8.  Pengujian, perbaikan, dan persiapan skenario presentasi.

Jangan mengorbankan alur utama yang sudah berfungsi demi fitur opsional.

## 9. Kriteria Selesai

Sebuah fitur hanya boleh dinyatakan selesai jika:

-   Kebutuhan dan perilakunya sesuai dengan brief.
-   Implementasi terhubung dengan database atau layanan yang benar,
    bukan sekadar tampilan statis.
-   Validasi dan kondisi gagal dasar ditangani.
-   Alur utama telah diuji.
-   Tidak merusak fitur yang sudah ada.
-   Hasil pengujian dilaporkan secara jujur.

Untuk integrasi Fonnte, bedakan dengan jelas: - Berhasil diuji. - Belum
diuji karena konfigurasi/akses. - Tidak didukung atau belum dapat
dipastikan.

## 10. Cara Berkomunikasi

-   Gunakan bahasa Indonesia yang jelas dan langsung.
-   Jelaskan istilah teknis seperlunya.
-   Jangan memberikan klaim keberhasilan tanpa bukti pengujian.
-   Jika ada beberapa opsi, rekomendasikan opsi paling sederhana dan
    realistis untuk MVP.
-   Jangan memperluas ruang lingkup tanpa persetujuan pengguna.
-   Ingat target utama: aplikasi lokal yang stabil dan siap
    didemonstrasikan, bukan sistem produksi kampus.

------------------------------------------------------------------------

## Dokumen Referensi Proyek

Jika tersedia di root workspace, gunakan dokumen berikut sebagai konteks
kebutuhan:

-   `konsep_crm_layanan_mahasiswa_unpam.md` --- ringkasan konsep dan
    keputusan diskusi proyek.

Jika dokumen referensi bertentangan dengan keputusan terbaru pengguna,
tanyakan atau ikuti keputusan terbaru yang eksplisit.
