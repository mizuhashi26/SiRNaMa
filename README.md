# CRM Pengelolaan Informasi, Reminder, dan Layanan Mahasiswa Terintegrasi WhatsApp

**Studi Kasus:** Universitas Pamulang (UNPAM)  
**Status Proyek:** Aplikasi Mandiri (*Standalone*) untuk Tugas Mata Kuliah (Simulasi Akademik)  
**Lingkungan Pengembangan:** Lokal via Laragon (PHP 8.3, MySQL 8.4, Laravel 11, Tailwind CSS)

> **Catatan Penting:** Aplikasi ini berdiri sendiri (*standalone*) dan **bukan sistem resmi UNPAM**. Seluruh data mahasiswa, nomor WhatsApp, informasi kampus, dan jadwal pembayaran di dalam aplikasi merupakan data simulasi (*dummy*) untuk keperluan demonstrasi akademik.

---

## 1. Fitur Utama MVP

1. **Pengelolaan Informasi Kampus Satu Pintu (`CampusInformation`):**
   - Admin mengelola informasi berdasarkan kategori (`Akademik`, `Kalender Akademik`, `Jadwal Perkuliahan & Ujian`, `Pengumuman Kampus`, `Kegiatan & Agenda`, `Administrasi Layanan`, dan `Informasi Pembayaran Kuliah`).
   - Data yang dipublikasikan otomatis menjadi sumber data bersama (*single source of truth*) untuk **Halaman Website Publik**, **Chatbot Website**, dan **Chatbot WhatsApp**.
2. **Reminder Pembayaran Kuliah Umum (`PaymentReminder`):**
   - Berlaku untuk pengingat pembayaran umum seluruh mahasiswa aktif (bukan perhitungan tagihan individual).
   - Mendukung **Kirim Manual Sekarang** melalui tombol di dashboard dan **Pengiriman Terjadwal Otomatis** melalui Laravel Scheduler (`php artisan reminders:send-due`).
   - Mencatat log pengiriman per mahasiswa secara jujur (`sent`, `failed`, atau `unverified_no_token` bila `FONNTE_TOKEN` belum diisi).
3. **Chatbot Website Berbasis Menu (`WebChatController` & `ChatbotMenuService`):**
   - Widget chat interaktif di pojok kanan bawah halaman publik.
   - Menggunakan tombol pilihan menu (`1. Informasi Kampus`, `2. Informasi Pembayaran`, `3. Hubungi Admin`) tanpa AI generatif.
4. **Chatbot WhatsApp via Fonnte (`FonnteWebhookController`):**
   - Menerima pesan masuk melalui webhook `POST /api/webhook/fonnte`.
   - Menyajikan menu angka/keyword yang konsisten dengan Chatbot Website dan mengirim balasan melalui API Fonnte (`https://api.fonnte.com/send`).
5. **Live Chat Admin Terpusat (`LiveChatController`):**
   - Menyatukan percakapan dari kanal **Website** dan **WhatsApp** dalam satu menu Live Chat.
   - Mendukung siklus status:
     - `BOT_ACTIVE` (Bot melayani menu pilihan)
     - `WAITING_ADMIN` (Mahasiswa memilih `3. Hubungi Admin`)
     - `ADMIN_ACTIVE` (Admin menekan **Ambil Alih** dan membalas dari dashboard)
     - `CLOSED` (Admin menekan **Akhiri Percakapan**; interaksi berikutnya kembali dilayani oleh Bot)

---

## 2. Akun Demo Default (Hasil Seeder)

- **URL Website Publik:** `http://localhost:8000` (atau melalui virtual host Laragon)
- **URL Login Admin:** `http://localhost:8000/login`
- **Email Admin:** `admin@unpam-simulasi.ac.id`
- **Password Admin:** `password123`

---

## 3. Cara Menjalankan Secara Lokal di Laragon

1. Pastikan layanan **MySQL** di Laragon aktif.
2. Jalankan migrasi dan seeder (jika belum dijalankan):
   ```powershell
   C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate --seed
   ```
3. Build aset tampilan (Tailwind CSS & Vite):
   ```powershell
   npm run build
   ```
4. Jalankan server pengembangan lokal:
   ```powershell
   C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve
   ```
5. Untuk mengaktifkan pengiriman **Reminder Terjadwal Otomatis** di lokal:
   ```powershell
   C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan schedule:work
   ```

---

## 4. Konfigurasi Integrasi WhatsApp (Fonnte) & Webhook Lokal

1. Buka file `.env` dan isi token perangkat Fonnte Anda:
   ```env
   FONNTE_TOKEN=isi_token_fonnte_anda_di_sini
   FONNTE_API_URL=https://api.fonnte.com/send
   FONNTE_WEBHOOK_SECRET=
   ```
2. Untuk menguji **Pesan Masuk WhatsApp (Webhook Fonnte)** ke komputer lokal, jalankan tunnel `ngrok` bawaan Laragon:
   ```powershell
   C:\laragon\bin\ngrok\ngrok.exe http 8000
   ```
3. Salin URL HTTPS dari `ngrok` (contoh: `https://xxxx.ngrok-free.app`) lalu tempelkan ke pengaturan **Webhook URL** di dashboard Fonnte dengan akhiran:
   ```text
   https://xxxx.ngrok-free.app/api/webhook/fonnte
   ```
4. **Catatan Batasan Fonnte:** Gunakan ruang **Live Chat di Dashboard Admin** untuk membalas pesan mahasiswa agar seluruh riwayat percakapan tercatat rapi dan terkirim melalui API Fonnte. Membalas langsung dari aplikasi WhatsApp di ponsel tidak otomatis menyinkronkan pesan keluar ke dashboard CRM.

---

## 5. Pengujian Otomatis

Jalankan seluruh pengujian fitur (Feature Test) dengan perintah:
```powershell
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan test
```
