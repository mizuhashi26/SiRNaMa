# SiRNaMa (Sistem Informasi, Reminder, dan Layanan Mahasiswa)

Platform CRM layanan akademik & kemahasiswaan terintegrasi WhatsApp Fonnte, Chatbot Web, dan Live Chat Admin terpusat. Dirancang dengan prinsip **Dual-Environment (Local Laragon & Instant Serverless Hosting Ready)** serta model multi-kampus (*white-label generic*).

---

## 1. Fitur Utama Sistem

1. **Pengelolaan Informasi Kampus Satu Pintu (`CampusInformation`):**
   - Admin mengelola informasi berbasis kategori: *Akademik*, *Kalender Akademik*, *Jadwal Perkuliahan & Ujian*, *Pengumuman Kampus*, *Kegiatan & Agenda*, *Administrasi Layanan*, dan *Informasi Pembayaran*.
   - Data yang dipublikasikan menjadi satu sumber kebenaran tunggal (*Single Source of Truth*) yang otomatis menyuplai portal web publik, chatbot website, dan chatbot WhatsApp Fonnte.
2. **Dual-Channel Chatbot (Web & WhatsApp Fonnte):**
   - Chatbot responsif berbasis angka dan kata kunci terstruktur tanpa ketergantungan AI generatif.
   - Sinkronisasi instan dengan perubahan data kampus dan informasi terbaru.
3. **Live Chat Admin Terpusat (`LiveChatController`):**
   - Eskalasi otomatis percakapan dari mode `BOT_ACTIVE` ke `WAITING_ADMIN`.
   - Admin dapat mengambil alih percakapan (`ADMIN_ACTIVE`), membalas langsung ke nomor WhatsApp mahasiswa melalui API Fonnte, dan mengembalikan sesi ke bot (`CLOSED` / `BOT_ACTIVE`).
   - Pencatatan identitas otomatis (NIM, Nama Mahasiswa, nomor kontak, serta kanal asal percakapan).
4. **Reminder Akademik & Pembayaran (`PaymentReminder`):**
   - Pengiriman pengingat manual maupun otomatis berbasis scheduler.
   - Pilihan target penerima yang fleksibel: seluruh mahasiswa aktif, mahasiswa per kelas reguler, personal per mahasiswa, atau nomor kustom.
   - Audit log pengiriman per nomor mahasiswa secara real-time.
5. **Keamanan & Otorisasi Berlapis:**
   - 3 Role Akun: `Superadmin`, `Admin Reguler`, dan `Admin Chat` (dibatasi khusus live chat).
   - Rate limiting / throttling `throttle:60,1` pada login, chatbot web, dan webhook.
   - Header proteksi no-cache (`PreventBackHistory`) untuk memblokir celah tombol back setelah logout.
   - Validasi ketat payload webhook WhatsApp (anti-fuzzing & filter nomor telepon).

---

## 2. Akun Demo Default (Hasil Seeder)

- **URL Portal Publik:** `http://localhost/crm_layanan_mahasiswa/public/` (atau `http://127.0.0.1:8000`)
- **URL Login Admin:** `http://localhost/crm_layanan_mahasiswa/public/login` (atau `http://127.0.0.1:8000/login`)
- **Akun Superadmin:**
  - Email: `superadmin@unpam.ac.id` (atau `admin@unpam.ac.id`)
  - Password: `password123`
- **Akun Admin Chat:**
  - Email: `adminchat@unpam.ac.id`
  - Password: `password123`

---

## 3. Cara Menjalankan Secara Lokal di Laragon

1. Pastikan modul **Apache** dan **MySQL** di Laragon aktif.
2. Pasang dependensi dan lakukan migrasi + seeder:
   ```powershell
   composer install
   php artisan migrate --seed
   ```
3. Kompilasi aset frontend:
   ```powershell
   npm run build
   ```
4. Jalankan scheduler reminder otomatis di background (opsional):
   ```powershell
   php artisan schedule:work
   ```

---

## 4. Konfigurasi WhatsApp (Fonnte) & Webhook

1. Masukkan token Fonnte ke file `.env` (atau melalui menu Pengaturan di Dashboard Superadmin):
   ```env
   FONNTE_TOKEN=token_fonnte_anda
   FONNTE_API_URL=https://api.fonnte.com/send
   FONNTE_WEBHOOK_SECRET=
   ```
2. Saat pengujian di komputer lokal, gunakan tunneling (seperti Cloudflare Tunnel atau ngrok) agar webhook cloud Fonnte dapat mencapai komputer lokal:
   ```powershell
   ngrok http 8000
   ```
3. Arahkan Webhook URL di dashboard Fonnte ke endpoint:
   ```text
   https://domain-tunnel-anda/api/webhook/fonnte
   ```

---

## 5. Pengujian Sistem Otomatis

Seluruh alur fitur, keamanan role, validasi webhook, dan isolasi data telah diverifikasi melalui rangkaian pengujian otomatis end-to-end:
```powershell
php artisan test
```
Hasil: **40 pengujian lulus 100% (321 assertions pass)**.
