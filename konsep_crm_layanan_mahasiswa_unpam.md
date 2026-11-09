# Dokumen Konsep Proyek CRM Layanan Mahasiswa UNPAM

**Status:** Konsep awal / dasar pengembangan MVP\
**Studi kasus:** Universitas Pamulang (UNPAM)\
**Target:** Aplikasi dapat didemonstrasikan dalam 4 hari\
**Lingkungan pengembangan:** Lokal menggunakan Laragon

------------------------------------------------------------------------

## 1. Gambaran Umum

Proyek ini adalah sistem Customer Relationship Management (CRM) untuk
membantu pengelolaan informasi, pengingat pembayaran kuliah, dan layanan
mahasiswa melalui website serta WhatsApp.

Sistem dibuat secara **standalone** untuk kebutuhan tugas mata kuliah.
Aplikasi tidak terhubung dengan database akademik maupun keuangan resmi
UNPAM. Data mahasiswa, informasi, dan tagihan yang digunakan selama
pengembangan dan presentasi berupa data simulasi (*dummy*).

Website menggunakan identitas visual UNPAM sebagai studi kasus.
Informasi dan jadwal pembayaran yang ditampilkan merupakan contoh untuk
demonstrasi, bukan pengumuman resmi kampus.

## 2. Tujuan Sistem

1.  Memusatkan pengelolaan informasi kampus dalam satu aplikasi.
2.  Memudahkan mahasiswa memperoleh informasi melalui website dan
    chatbot WhatsApp.
3.  Mengirim pengingat pembayaran kuliah secara otomatis maupun manual.
4.  Menyediakan layanan percakapan mahasiswa dengan admin melalui
    website dan WhatsApp.
5.  Menyimpan percakapan dari kedua kanal dalam satu dashboard CRM.

## 3. Pengguna Sistem

  -----------------------------------------------------------------------
  Pengguna                            Hak akses dan aktivitas
  ----------------------------------- -----------------------------------
  Admin                               Login ke dashboard, mengelola data
                                      mahasiswa, informasi kampus,
                                      reminder, serta menangani
                                      percakapan mahasiswa.

  Mahasiswa/pengunjung                Mengakses halaman informasi website
                                      dan menggunakan chatbot website
                                      atau WhatsApp. Tidak perlu login ke
                                      website untuk versi awal.
  -----------------------------------------------------------------------

Untuk MVP saat ini hanya ada dua jenis pengguna tersebut. Mahasiswa
berinteraksi melalui kanal layanan, bukan melalui dashboard khusus.

## 4. Modul dan Fitur Utama

### 4.1 Pengelolaan Informasi Kampus

Admin mengelola informasi melalui dashboard. Informasi yang
dipublikasikan menjadi sumber informasi bersama untuk:

-   Halaman informasi pada website.
-   Chatbot website.
-   Chatbot WhatsApp.

Contoh kategori informasi:

-   Akademik dan kalender akademik.
-   Jadwal perkuliahan atau ujian.
-   Pengumuman kampus.
-   Kegiatan dan agenda kampus.
-   Informasi administrasi.

Admin dapat menambah, melihat, mengubah, menghapus, dan memublikasikan
informasi. Informasi cukup dikelola satu kali agar isi yang ditampilkan
pada setiap kanal konsisten.

### 4.2 Reminder Pembayaran Kuliah

Versi awal menggunakan **reminder tagihan umum untuk mahasiswa**, bukan
tagihan individual.

Admin dapat:

-   Membuat informasi atau jadwal reminder pembayaran umum.
-   Menentukan tanggal dan waktu pengiriman.
-   Menentukan isi pesan reminder.
-   Mengirim reminder secara manual melalui tombol kirim.
-   Menjadwalkan pengiriman otomatis.

Pesan dikirim kepada nomor WhatsApp mahasiswa yang terdaftar pada data
simulasi.

Batasan MVP:

-   Tidak menghitung tagihan individual.
-   Tidak memeriksa status pembayaran masing-masing mahasiswa.
-   Tidak menyediakan payment gateway.
-   Tidak melakukan sinkronisasi dengan sistem keuangan kampus.

### 4.3 Chatbot WhatsApp

WhatsApp diintegrasikan menggunakan **Fonnte** sebagai gateway.

Chatbot versi awal menggunakan menu pilihan atau keyword sederhana,
bukan AI generatif.

Contoh menu utama:

1.  Informasi Kampus
2.  Informasi Pembayaran
3.  Hubungi Admin

Chatbot mengambil informasi dari data yang dikelola melalui dashboard
CRM. Menu informasi pembayaran menampilkan pengumuman atau jadwal
pembayaran umum yang tersedia.

### 4.4 Chatbot Website

Website menyediakan tombol atau widget chat. Chatbot menggunakan menu
pilihan yang konsisten dengan chatbot WhatsApp.

Contoh menu:

1.  Informasi Kampus
2.  Informasi Pembayaran
3.  Hubungi Admin

Pengunjung dapat memilih menu tanpa harus mengetik pertanyaan bebas.
Jika memilih Hubungi Admin, percakapan diteruskan ke dashboard CRM.

### 4.5 Live Chat Admin Terintegrasi

Percakapan dari website dan WhatsApp ditampilkan dalam satu menu Live
Chat pada dashboard admin.

Admin dapat melihat kanal asal percakapan, isi pesan, waktu pesan, dan
status percakapan.

Fitur pengambilalihan percakapan menggunakan mekanisme manual:

1.  Mahasiswa memilih Hubungi Admin.
2.  Percakapan masuk ke dashboard dengan status **Menunggu Admin**.
3.  Admin menekan tombol **Ambil Alih**.
4.  Status berubah menjadi **Admin Aktif**.
5.  Admin membalas melalui dashboard CRM atau mengambil alih dan
    membalas melalui WhatsApp.
6.  Setelah selesai, admin menekan **Akhiri Percakapan**.
7.  Status berubah menjadi **Selesai** dan chatbot dapat kembali
    melayani percakapan berikutnya.

Status percakapan yang direncanakan:

  Status           Keterangan
  ---------------- --------------------------------------
  Bot Aktif        Percakapan sedang ditangani chatbot.
  Menunggu Admin   Mahasiswa meminta bantuan admin.
  Admin Aktif      Percakapan telah diambil alih admin.
  Selesai          Percakapan telah diakhiri admin.

**Catatan integrasi:** Pengiriman pesan keluar menggunakan Fonnte API.
Pesan masuk memerlukan webhook Fonnte. Pengiriman balasan dari dashboard
dan sinkronisasi pesan yang dibalas langsung melalui WhatsApp perlu
diuji sesuai kemampuan event/webhook Fonnte yang tersedia. Jangan
menganggap pesan dari semua kanal otomatis tersinkron sebelum pengujian
berhasil.

### 4.6 Riwayat Interaksi (Opsional)

Fitur ini menjadi nilai tambah, bukan prioritas utama MVP.

Data yang dapat disimpan:

-   Identitas atau nomor kontak pengunjung.
-   Kanal komunikasi (Website atau WhatsApp).
-   Isi percakapan.
-   Waktu percakapan.
-   Status percakapan.

Analisis lanjutan seperti topik yang sering ditanyakan dan jumlah
keluhan dapat ditunda.

## 5. Gambaran Alur Sistem

### 5.1 Alur Pengelolaan Informasi

1.  Admin login ke dashboard.
2.  Admin menambahkan atau memperbarui informasi kampus.
3.  Admin memublikasikan informasi.
4.  Informasi tersedia pada halaman website.
5.  Chatbot website dan WhatsApp mengambil informasi yang sama.

### 5.2 Alur Reminder Pembayaran

1.  Admin membuat reminder pembayaran umum.
2.  Admin mengisi jadwal dan isi pesan.
3.  Sistem menyimpan reminder.
4.  Reminder dapat dikirim manual oleh admin atau otomatis sesuai
    jadwal.
5.  Fonnte mengirim pesan ke nomor mahasiswa yang terdaftar.

### 5.3 Alur Layanan Chat

1.  Mahasiswa membuka chatbot website atau mengirim pesan ke WhatsApp.
2.  Chatbot menampilkan menu pilihan.
3.  Mahasiswa memilih informasi atau Hubungi Admin.
4.  Jika meminta admin, percakapan masuk ke dashboard.
5.  Admin mengambil alih percakapan secara manual.
6.  Admin membalas dan mengakhiri percakapan.

## 6. Kanal dan Komponen Sistem

  -----------------------------------------------------------------------
  Komponen                            Fungsi
  ----------------------------------- -----------------------------------
  Website publik                      Menampilkan informasi kampus dan
                                      menyediakan chatbot.

  Dashboard admin                     Mengelola informasi, data
                                      mahasiswa, reminder, dan
                                      percakapan.

  Chatbot website                     Melayani pilihan informasi dan
                                      permintaan bantuan admin.

  Chatbot WhatsApp                    Melayani pilihan informasi dan
                                      permintaan bantuan admin melalui
                                      WhatsApp.

  Fonnte                              Gateway untuk pengiriman dan
                                      penerimaan pesan WhatsApp.

  Backend dan database                Menyimpan data serta menjalankan
                                      logika aplikasi.

  Scheduler                           Menjalankan pengiriman reminder
                                      otomatis sesuai jadwal.
  -----------------------------------------------------------------------

## 7. Lingkungan dan Batasan Pengembangan

-   Pengembangan dilakukan secara lokal menggunakan Laragon.
-   Database menggunakan MySQL lokal.
-   Website dan dashboard diakses melalui `localhost`.
-   Belum menggunakan hosting atau deployment.
-   Data mahasiswa dan informasi untuk presentasi menggunakan data
    dummy.
-   Integrasi WhatsApp menggunakan Fonnte.
-   Webhook Fonnte membutuhkan URL yang dapat diakses dari internet.
    Untuk pengujian aplikasi lokal, diperlukan tunnel seperti Ngrok atau
    Cloudflare Tunnel.
-   Chatbot menggunakan menu dan keyword sederhana agar realistis
    diselesaikan dalam waktu pengerjaan yang singkat.

## 8. Identitas Visual dan Penggunaan Data UNPAM

Aplikasi menggunakan UNPAM sebagai identitas studi kasus dan dapat
mengadaptasi logo, warna, serta nuansa visual kampus.

Namun, aplikasi ini merupakan simulasi akademik:

-   Tidak terhubung dengan sistem resmi UNPAM.
-   Tidak menggunakan data pribadi mahasiswa sungguhan tanpa izin.
-   Informasi dan jadwal pembayaran yang dibuat adalah data contoh.
-   Tampilan dan pesan perlu diberi keterangan bahwa konten merupakan
    simulasi apabila berpotensi dianggap sebagai informasi resmi.

## 9. Prioritas MVP

### Prioritas wajib

-   Login admin.
-   Dashboard admin sederhana.
-   CRUD data mahasiswa dan nomor WhatsApp.
-   CRUD informasi kampus.
-   Halaman website untuk menampilkan informasi.
-   Pengelolaan reminder pembayaran umum.
-   Pengiriman reminder manual melalui Fonnte.
-   Chatbot WhatsApp berbasis menu.
-   Chatbot website berbasis menu.
-   Pesan dari mahasiswa dapat masuk ke dashboard.
-   Admin dapat mengambil alih dan membalas percakapan dari dashboard.
-   Status percakapan dapat diperbarui.

### Prioritas jika waktu memungkinkan

-   Pengiriman reminder otomatis menggunakan scheduler.
-   Pengambilan alih dan balasan langsung melalui WhatsApp.
-   Penyelarasan riwayat pesan dari WhatsApp dan dashboard.
-   Riwayat interaksi dan ringkasan statistik.

## 10. Rencana Pengerjaan Empat Hari

  -----------------------------------------------------------------------
  Hari                    Fokus                   Target
  ----------------------- ----------------------- -----------------------
  Hari 1                  Fondasi aplikasi        Database, login admin,
                                                  dashboard, data
                                                  mahasiswa, dan
                                                  informasi kampus.

  Hari 2                  Reminder                Data reminder umum,
                                                  pengiriman manual
                                                  melalui Fonnte, dan
                                                  penjadwalan otomatis
                                                  jika memungkinkan.

  Hari 3                  Chatbot dan live chat   Chatbot website,
                                                  chatbot WhatsApp,
                                                  webhook pesan masuk,
                                                  serta tampilan
                                                  percakapan admin.

  Hari 4                  Integrasi dan pengujian Uji alur utama,
                                                  perbaiki kesalahan,
                                                  siapkan data demo, dan
                                                  persiapkan presentasi.
  -----------------------------------------------------------------------

Jadwal ini merupakan target kerja. Jika waktu terbatas, prioritaskan
alur yang dapat didemonstrasikan dari awal sampai akhir daripada
menambah fitur baru.

## 11. Skenario Demonstrasi Presentasi

1.  Admin login ke dashboard.
2.  Admin menambahkan informasi kampus.
3.  Informasi tampil di halaman website.
4.  Mahasiswa membuka chatbot website dan memilih Informasi Kampus.
5.  Mahasiswa mengakses chatbot WhatsApp dan memilih Informasi
    Pembayaran.
6.  Admin membuat reminder pembayaran umum dan mengirimkannya melalui
    Fonnte.
7.  Mahasiswa meminta Hubungi Admin melalui salah satu kanal.
8.  Pesan masuk ke dashboard CRM.
9.  Admin menekan Ambil Alih dan membalas pesan.
10. Admin mengakhiri percakapan.

## 12. Hal yang Belum Dikunci

Hal-hal berikut dapat diputuskan ketika masuk tahap perancangan teknis:

-   Framework dan versi final yang digunakan.
-   Detail desain halaman website dan dashboard.
-   Struktur tabel database.
-   Format dan jadwal reminder.
-   Detail menu chatbot dan keyword.
-   Mekanisme identifikasi percakapan website dan WhatsApp.
-   Ketersediaan serta perilaku webhook Fonnte untuk sinkronisasi pesan.
-   Detail pengujian scheduler dan tunnel lokal.

------------------------------------------------------------------------

**Dokumen ini adalah acuan konsep awal proyek.** Perubahan ruang lingkup
dapat dilakukan setelah mempertimbangkan waktu pengerjaan dan hasil uji
integrasi.
