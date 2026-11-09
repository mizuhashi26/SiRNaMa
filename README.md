# CRM Pengelolaan Informasi, Reminder, dan Layanan Mahasiswa Terintegrasi WhatsApp

**Studi Kasus:** Universitas Pamulang (UNPAM)  
**Lingkungan Pengembangan:** Lokal via Laragon (PHP 8.3, MySQL 8.4, Laravel 11, Tailwind CSS)

---

## 1. Fitur Utama MVP

1. **Pengelolaan Informasi Kampus Satu Pintu (`CampusInformation`):**
   - Admin mengelola informasi berdasarkan kategori (`Akademik`, `Kalender Akademik`, `Jadwal Perkuliahan & Ujian`, `Pengumuman Kampus`, `Kegiatan & Agenda`, `Administrasi Layanan`, dan `Informasi Pembayaran Kuliah`).
   - Data yang dipublikasikan otomatis menjadi sumber data bersama (*single source of truth*) untuk **Halaman Website Publik**, **Chatbot Website**, dan **Chatbot WhatsApp**.
2. **Reminder Pembayaran Kuliah Umum (`PaymentReminder`):**
   - Berlaku untuk pengingat pembayaran umum seluruh mahasiswa aktif.
   - Mendukung **Kirim Manual Sekarang** melalui tombol di dashboard dan **Pengiriman Terjadwal Otomatis** melalui Laravel Scheduler (`php artisan reminders:send-due`).
   - Mencatat log pengiriman per mahasiswa secara jujur (`sent`, `failed`, atau `unverified_no_token` bila `FONNTE_TOKEN` belum diisi).
3. **Chatbot Website Berbasis Menu (`WebChatController` & `ChatbotMenuService`):**
   - Widget chat modern di pojok kanan bawah halaman publik.
   - Tombol trigger berbentuk pill ramping dengan **lingkaran indikator hijau berkedip dinamis** (`animate-ping`).
   - **Fitur Draggable:** Widget dan tombol dapat digeser/dipindahkan posisinya secara bebas di layar monitor maupun ponsel (mouse & touch drag).
   - **Scroll Tetap Terjaga (Stay):** Posisi scroll tidak dipaksa loncat ke bawah saat mengirim pesan atau polling pesan baru.
   - Menggunakan tombol pilihan menu (`1. Informasi Kampus`, `2. Informasi Pembayaran`, `3. Hubungi Admin`) tanpa AI generatif.
4. **Chatbot WhatsApp via Fonnte (`FonnteWebhookController`):**
   - Menerima pesan masuk melalui webhook `POST /api/webhook/fonnte`.
   - Menyajikan menu angka/keyword yang konsisten dengan Chatbot Website dan mengirim balasan melalui API Fonnte (`https://api.fonnte.com/send`).
5. **Live Chat Admin Terpusat (`LiveChatController`):**
   - Menyatukan percakapan dari kanal **Website** dan **WhatsApp** dalam satu menu Live Chat di dashboard admin.
   - Alur status interaksi:
     - `BOT_ACTIVE` (Bot melayani menu pilihan)
     - `WAITING_ADMIN` (Mahasiswa memilih `3. Hubungi Admin`)
     - `ADMIN_ACTIVE` (Admin menekan **Ambil Alih Percakapan** dan membalas dari dashboard)
     - **Tinggalkan (Kembali ke Bot):** Saat admin meninggalkan percakapan, status otomatis dialihkan kembali ke `BOT_ACTIVE` agar mahasiswa langsung dilayani kembali oleh Chatbot tanpa sesi menggantung.

---

## 2. Akun Admin Default (Hasil Seeder)

- **URL Website Publik:** `http://localhost:8000` (atau via Laragon: `http://localhost/crm_layanan_mahasiswa/public/`)
- **URL Login Admin:** `http://localhost:8000/login`
- **Email Admin:** `admin@unpam.ac.id`
- **Password Admin:** `password123`

---

## 3. Cara Menjalankan Secara Lokal di Laragon

1. Pastikan layanan **MySQL** dan **Apache** di Laragon aktif.
2. Jalankan migrasi dan seeder database:
   ```powershell
   C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate --seed
   ```
3. Kompilasi aset frontend (Tailwind CSS palet Deep Spruce & Teal):
   ```powershell
   npm run build
   ```
4. Jalankan server pengembangan lokal (jika tidak menggunakan Apache Laragon langsung):
   ```powershell
   C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve --host=127.0.0.1 --port=8000
   ```
5. Untuk mengaktifkan pengiriman **Reminder Terjadwal Otomatis** di lokal:
   ```powershell
   C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan schedule:work
   ```

---

## 4. Konfigurasi Integrasi WhatsApp (Fonnte) & Webhook Lokal

1. Buka file `.env` dan isi token perangkat Fonnte:
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
4. **Catatan Praktik Live Chat:** Balas percakapan melalui **Live Chat di Dashboard Admin** agar seluruh riwayat percakapan tercatat rapi dan pesan keluar terkirim via API Fonnte.

---

## 5. Pengujian Otomatis

Jalankan seluruh pengujian end-to-end dengan perintah:
```powershell
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan test
```
Semua 7 pengujian fitur mencakup Autentikasi Admin, Single Source of Truth Informasi, Web Chatbot, WhatsApp Webhook, Takeover/Reply/Leave Live Chat, dan Reminder Pembayaran otomatis lolos pengujian (55 assertions PASS).
