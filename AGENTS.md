# AGENTS.md --- SiRNaMa (Sistem Informasi, Reminder, dan Layanan Mahasiswa)

Dokumen ini adalah aturan kerja utama untuk AI coding agent di workspace proyek **SiRNaMa**. Baca dan patuhi seluruh instruksi sebelum melakukan perubahan kode.

## ⚠️ ATURAN EMAS: STRICT CODE & UI FREEZE (WAJIB DIPATUHI)

- **DILARANG MENGUBAH APA PUN TANPA INSTRUKSI EKSPLISIT:** Untuk kondisi sistem saat ini, **TIDAK ADA** yang boleh diubah mulai dari tampilan (UI/UX, warna, layout, komponen, teks, CSS) hingga sisi belakang (_backend_, logika bisnis, _controller_, _routing_, skema database, maupun konfigurasi) **kecuali bagian yang secara spesifik diminta oleh pengguna**.
- **JANGAN BERINISIATIF MEROMBAK KODE:** Dilarang melakukan _refactoring_ otomatis, "merapikan" kode yang sudah jalan, memperbarui _styling_ di halaman lain, atau memodifikasi fitur di luar cakupan perintah pengguna saat itu.
- **ISOLASI PERUBAHAN:** Jika pengguna meminta perbaikan bug di Fitur A atau penambahan Fitur B, sentuh **HANYA** baris kode atau file yang berkaitan langsung dengan permintaan tersebut. Biarkan seluruh bagian sistem lainnya tetap utuh 100% seperti semula.

---

## 1. Konteks Proyek & Identitas Sistem

- **Nama Sistem:** **SiRNaMa** (_Sistem Informasi, Reminder, dan Layanan Mahasiswa_).
- **Cakupan Sistem:** Platform CRM akademik generik (_white-label_ / _multi-kampus_) yang dapat digunakan oleh perguruan tinggi mana saja, tidak terbatas pada satu kampus tertentu.
- **Fase Saat Ini:** **Maturing, Bug Minimization, Feature Polishing & Zero-Friction Deployment Readiness**.
- **Kondisi Proyek:** Fitur dasar MVP serta integrasi dua arah Chatbot Web, WhatsApp (Fonnte), dan alur Live Chat Admin sudah berjalan baik dan dikunci (_locked_) dari perubahan tanpa izin.

## 2. Arsitektur Dual-Environment (Local Testing == Instant Hosting Ready)

Setiap penambahan fitur atau perbaikan bug yang diminta harus menganut prinsip **100% Environment Parity** (kode yang berjalan di Laragon lokal harus siap di-_deploy_ ke hosting detik itu juga tanpa perlu _refactor_ ulang):

1. **Zero Hardcoded URLs & Credentials:**
    - Dilarang menulis URL `localhost`, `127.0.0.1`, nama domain statis, atau kredensial/token secara _hardcode_ di dalam _source code_.
    - Selalu gunakan variabel konfigurasi dari `.env` (seperti `APP_URL`, `APP_ENV`, `DB_*`, `FONNTE_TOKEN`) dan helper dinamis bawaan framework/bahasa pemrograman.
2. **Relative & Dynamic Paths:**
    - Gunakan _path_ relatif atau helper routing/asset dinamis untuk seluruh pemanggilan gambar, CSS/JS, endpoint AJAX, dan webhook agar tidak terjadi _broken link_ atau _Mixed Content (HTTP/HTTPS)_ saat naik ke hosting.
3. **Database & Migration Consistency:**
    - Setiap penambahan fitur yang membutuhkan perubahan tabel/kolom wajib dicatat secara rapi (melalui _migration_ atau skrip SQL terstruktur) tanpa merusak tabel/data yang sudah ada.
4. **Case-Sensitivity Awareness:**
    - Mengingat Laragon berjalan di Windows (_case-insensitive_) sedangkan mayoritas server hosting berjalan di Linux (_case-sensitive_), pastikan penulisan nama file, folder, _class_, dan _import_ konsisten 100% secara huruf besar/kecil.

## 3. Pengguna dan Modul Inti SiRNaMa

### Pengguna:

1. **Admin Kampus:** Mengelola informasi akademik, mengatur jadwal/pengiriman reminder, dan melayani mahasiswa melalui Live Chat terpusat.
2. **Mahasiswa / Pengunjung:** Mengakses informasi dan layanan melalui portal web publik, chatbot website, atau WhatsApp tanpa perlu login.

### Modul Sistem:

- **Pengelolaan Informasi Kampus (Single Source of Truth):** Satu basis data informasi umum (akademik, kalender, jadwal, pengumuman, administrasi) yang otomatis menyuplai halaman web publik, chatbot website, dan chatbot WhatsApp.
- **Reminder Pembayaran & Akademik Umum:** Pengelolaan dan pengiriman pengingat terpusat (manual maupun terjadwal) ke daftar kontak WhatsApp mahasiswa.
- **Dual-Channel Chatbot (Web & WhatsApp Fonnte):** Chatbot berbasis menu/kata kunci yang responsif dan konsisten di kedua kanal.
- **Live Chat Admin Terpusat:** Menangani eskalasi dari mode `BOT_ACTIVE` ke `WAITING_ADMIN`, `ADMIN_ACTIVE`, hingga `CLOSED` (dilengkapi pencatatan identitas NIM, Nama, dan keperluan mahasiswa).

## 4. Prinsip UI/UX: Modern Fungsional (Strict Anti-Slop)

_(Catatan: Aturan UI/UX di bawah ini HANYA berlaku ketika pengguna secara eksplisit meminta pembuatan halaman/komponen baru atau meminta perbaikan tampilan tertentu. Jangan mengubah UI yang sudah ada jika tidak diminta)._

- **Modern, Bukan Futuristik:** Gunakan pendekatan desain _clean enterprise_ yang menyatu dengan desain SiRNaMa yang sudah ada. Gunakan permukaan warna netral yang bersih, kontras teks yang tajam, tipografi yang jelas, dan _spacing_ yang terukur.
- **LARANGAN VISUAL (Anti-Slop):**
    - Dilarang menggunakan gaya _cyberpunk_, _neon glow_, _glassmorphism_ berlebihan, atau _gradient_ warna-warni acak khas template AI generik.
    - Dilarang menambahkan dekorasi tanpa fungsi, kartu bertumpuk yang tidak perlu, atau animasi yang memperlambat kerja admin.
- **Branding SiRNaMa:** Gunakan nama dan identitas **SiRNaMa** secara konsisten pada fitur atau teks baru yang diminta.

## 5. Aturan Workflow untuk Agent

### Sebelum mengerjakan

1. Periksa kondisi file dan kode yang sudah ada di workspace.
2. Pastikan Anda memahami batas-batas kode yang **harus dibiarkan utuh**.
3. Untuk penambahan fitur atau perbaikan bug yang menyentuh beberapa file, sampaikan rencana singkat beserta daftar file yang akan disentuh sebelum mengeksekusi perubahan.

### Saat mengerjakan

- Kerjakan **hanya** apa yang diminta oleh pengguna pada giliran tersebut.
- Tulis kode yang defensif: tangani _error handling_, _empty state_, _loading state_, dan validasi input server maupun client agar meminimalisir potensi bug saat demo/produksi.
- Pastikan kode baru tidak merusak kompatibilitas antara lokal (Laragon) dan server hosting.

### Setelah mengerjakan

1. Lakukan verifikasi bahwa perubahan berjalan sesuai permintaan dan **tidak ada efek samping (regresi)** pada fitur atau tampilan lain.
2. Jelaskan secara ringkas file apa saja yang diubah dan cara mengujinya di lokal.

## 6. Pemanfaatan Plugin/Skills (Antigravity)

- **Caveman:** Gunakan sebagai mode utama untuk memastikan setiap perubahan kode maupun perbaikan bug dilakukan secara presisi (_surgical_) dan terisolasi tanpa menyentuh kode di luar instruksi.
- **Anti-Slop:** Gunakan saat pengguna meminta pembuatan komponen UI baru atau perbaikan tampilan agar hasilnya tetap bersih, modern-fungsional, dan selaras dengan tampilan yang sudah ada.
- **Superpowers:** Gunakan saat merancang fitur baru yang diminta pengguna dan memverifikasi kesiapan pengujian.

## 7. Fokus Pengerjaan Saat Ini

1. **Penambahan & Perbaikan Berdasarkan Instruksi:** Mengerjakan penambahan fitur atau perbaikan bug sesuai perintah spesifik pengguna tanpa mengubah bagian UI hingga backend yang tidak diminta.
2. **Minimalisasi Bug & Hardening:** Memastikan setiap fitur baru atau perbaikan yang dikerjakan bebas dari celah bug, aman, dan memiliki penanganan _error_ yang baik.
3. **Kesiapan Hosting Instan (Zero-Config Switch):** Memastikan aplikasi selalu siap di-_deploy_ ke layanan hosting kapan saja sambil tetap mempertahankan fungsi penuh yang identik di Laragon lokal untuk keperluan _testing_.
