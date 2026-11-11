<?php

namespace App\Services;

use App\Models\CampusInformation;
use App\Models\Conversation;
use App\Models\ConversationMessage;

class ChatbotMenuService
{
    public const CATEGORY_MENU_MAP = [
        '1.1' => 'akademik',
        '1.2' => 'kalender_akademik',
        '1.3' => 'jadwal_ujian',
        '1.4' => 'pengumuman',
        '1.5' => 'kegiatan',
        '1.6' => 'administrasi',
    ];

    public const PAYMENT_SUBMENU_MAP = [
        '2.1' => 'jadwal',
        '2.2' => 'saluran',
        '2.3' => 'kendala',
    ];

    /**
     * Proses pesan masuk dari pengunjung website atau WhatsApp.
     *
     * @return array{
     *   conversation: Conversation,
     *   user_message: ConversationMessage,
     *   bot_message: ?ConversationMessage,
     *   options: array<int, array{code: string, label: string}>
     * }
     */
    public function handleIncomingMessage(Conversation $conversation, string $rawInput, ?string $senderName = null): array
    {
        $input = trim($rawInput);
        $normalized = mb_strtolower($input);

        // Jika percakapan sebelumnya CLOSED, pesan baru membuka kembali sesi BOT_ACTIVE
        if ($conversation->status === Conversation::STATUS_CLOSED) {
            $conversation->status = Conversation::STATUS_BOT_ACTIVE;
            $conversation->closed_at = null;
            $conversation->assigned_admin_id = null;
            $conversation->save();
        }

        $userMessage = $conversation->messages()->create([
            'sender_type' => 'user',
            'sender_name' => $senderName ?? $conversation->user_name ?? 'Mahasiswa',
            'message' => $input,
            'delivery_status' => 'delivered',
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        // Jika sedang ADMIN_ACTIVE, bot tidak membalas agar tidak menimpa percakapan admin.
        if ($conversation->status === Conversation::STATUS_ADMIN_ACTIVE) {
            return [
                'conversation' => $conversation->fresh(),
                'user_message' => $userMessage,
                'bot_message' => null,
                'options' => [],
            ];
        }

        // Jika sedang WAITING_ADMIN, izinkan kembali ke menu bot jika ketik 0 / keluar / menu / batal.
        if ($conversation->status === Conversation::STATUS_WAITING_ADMIN) {
            if ($this->isExitToMainMenuCommand($normalized)) {
                $conversation->status = Conversation::STATUS_BOT_ACTIVE;
                $conversation->save();

                $replyText = "Permintaan bantuan admin dibatalkan. Anda kembali ke layanan Chatbot.\n\n" . $this->buildMainMenuText();
                $botMessage = $this->recordBotReply($conversation, $replyText);

                return [
                    'conversation' => $conversation->fresh(),
                    'user_message' => $userMessage,
                    'bot_message' => $botMessage,
                    'options' => $this->getMainMenuOptions(),
                ];
            }

            // Jika memilih 3 kembali saat sudah WAITING_ADMIN
            if (in_array($normalized, ['3', 'hubungi admin', 'admin'], true)) {
                $replyText = 'Percakapan Anda sudah berada dalam antrean Menunggu Admin. Silakan tulis pesan atau pertanyaan Anda, Admin akan segera mengambil alih.';
                $botMessage = $this->recordBotReply($conversation, $replyText);

                return [
                    'conversation' => $conversation->fresh(),
                    'user_message' => $userMessage,
                    'bot_message' => $botMessage,
                    'options' => [
                        ['code' => '0', 'label' => '0. Kembali ke Menu Layanan'],
                    ],
                ];
            }

            // Pesan teks bebas dari mahasiswa disimpan untuk dibaca admin
            return [
                'conversation' => $conversation->fresh(),
                'user_message' => $userMessage,
                'bot_message' => null,
                'options' => [
                    ['code' => '0', 'label' => '0. Kembali ke Menu Layanan'],
                ],
            ];
        }

        // Status BOT_ACTIVE: cek konteks sub-menu sebelum pesan baru ini
        $previousContext = $this->detectActiveMenuContext($conversation, $userMessage->id);
        $resolved = $this->resolveBotResponse($conversation, $normalized, $previousContext);

        $botMessage = $this->recordBotReply($conversation, $resolved['text']);

        return [
            'conversation' => $conversation->fresh(),
            'user_message' => $userMessage,
            'bot_message' => $botMessage,
            'options' => $resolved['options'],
        ];
    }

    protected function isExitToMainMenuCommand(string $normalized): bool
    {
        return in_array($normalized, ['0', 'keluar', 'menu', 'menu utama', 'utama', 'batal', 'kembali', 'exit', 'back'], true);
    }

    /**
     * @return array{text: string, options: array<int, array{code: string, label: string}>}
     */
    protected function resolveBotResponse(Conversation $conversation, string $normalized, string $previousContext = 'main'): array
    {
        // Perintah eksplisit 0 / keluar -> kembali ke Menu Utama
        if ($this->isExitToMainMenuCommand($normalized)) {
            return [
                'text' => $this->buildMainMenuText(),
                'options' => $this->getMainMenuOptions(),
            ];
        }

        // Menu 3: Hubungi Admin
        if (in_array($normalized, ['3', 'hubungi admin', 'admin', 'cs', 'bantuan'], true)) {
            $conversation->status = Conversation::STATUS_WAITING_ADMIN;
            $conversation->save();

            return [
                'text' => "Permintaan Anda telah masuk ke antrean dashboard CRM dengan status *Menunggu Admin*.\n\nSilakan ketik pertanyaan atau kendala Anda di bawah ini. Admin akan segera mengambil alih percakapan.\n(Ketik *0* jika ingin membatalkan dan kembali ke menu layanan).",
                'options' => [
                    ['code' => '0', 'label' => '0. Keluar / Menu Utama'],
                ],
            ];
        }

        // Menu 2: Informasi Pembayaran
        if (in_array($normalized, ['2', 'informasi pembayaran', 'pembayaran', 'bayar', 'tagihan', 'ukt'], true)) {
            return [
                'text' => $this->buildPaymentInformationText(),
                'options' => $this->getPaymentSubMenuOptions(),
            ];
        }

        // Sub-layanan Menu 2: 2.1, 2.2, 2.3
        if (isset(self::PAYMENT_SUBMENU_MAP[$normalized]) || in_array($normalized, ['2.1', '2.2', '2.3', 'jadwal pembayaran', 'saluran pembayaran', 'kendala pembayaran'], true)) {
            return [
                'text' => $this->buildPaymentSubDetailText($normalized),
                'options' => $this->getPaymentSubMenuOptions(), // Tetap di sub-layanan pembayaran sampai tekan 0!
            ];
        }

        // Menu 1: Informasi Kampus (daftar sub-kategori + ringkasan terbaru)
        if (in_array($normalized, ['1', 'informasi kampus', 'kampus'], true)) {
            return [
                'text' => $this->buildCampusCategoryOverviewText(),
                'options' => $this->getCampusCategoryOptions(),
            ];
        }

        // Sub-kategori Informasi Kampus (1.1 s/d 1.6 atau nama kategori)
        $matchedCategory = self::CATEGORY_MENU_MAP[$normalized] ?? $this->matchCategoryByKeyword($normalized);
        if ($matchedCategory !== null) {
            return [
                'text' => $this->buildCategoryItemsText($matchedCategory),
                'options' => $this->getCampusCategoryOptions(), // Tetap di sub-layanan kampus sampai tekan 0!
            ];
        }

        // Detail item spesifik: format "info <id>" atau "detail <id>"
        if (preg_match('/^(?:info|detail)\s*(\d+)$/', $normalized, $matches)) {
            $infoId = (int) $matches[1];
            $item = CampusInformation::published()->find($infoId);

            if ($item && $item->category === 'pembayaran') {
                return [
                    'text' => $this->buildSingleInformationText($infoId),
                    'options' => $this->getPaymentSubMenuOptions(),
                ];
            }

            return [
                'text' => $this->buildSingleInformationText($infoId),
                'options' => $this->getCampusCategoryOptions(),
            ];
        }

        // Jika user sedang berada di dalam Sub-Menu 1 (Informasi Kampus) dan belum menekan 0/keluar, tetap di Sub-Menu 1
        if ($previousContext === 'campus') {
            return [
                'text' => $this->buildCampusCategoryOverviewText(),
                'options' => $this->getCampusCategoryOptions(),
            ];
        }

        // Jika user sedang berada di dalam Sub-Menu 2 (Informasi Pembayaran) dan belum menekan 0/keluar, tetap di Sub-Menu 2
        if ($previousContext === 'payment') {
            return [
                'text' => $this->buildPaymentInformationText(),
                'options' => $this->getPaymentSubMenuOptions(),
            ];
        }

        // Default / Menu Utama
        return [
            'text' => $this->buildMainMenuText(),
            'options' => $this->getMainMenuOptions(),
        ];
    }

    protected function matchCategoryByKeyword(string $normalized): ?string
    {
        return match ($normalized) {
            'akademik' => 'akademik',
            'kalender', 'kalender akademik', 'kalender_akademik' => 'kalender_akademik',
            'jadwal', 'jadwal ujian', 'jadwal_ujian', 'ujian' => 'jadwal_ujian',
            'pengumuman', 'pengumuman kampus' => 'pengumuman',
            'kegiatan', 'agenda' => 'kegiatan',
            'administrasi', 'admin akademik' => 'administrasi',
            default => null,
        };
    }

    public function buildMainMenuText(): string
    {
        return "Halo! Selamat datang di Layanan Informasi Mahasiswa UNPAM.\n\n"
            . "Silakan pilih menu layanan dengan mengetik angka atau menekan tombol di bawah:\n"
            . "1. Informasi Kampus\n"
            . "2. Informasi Pembayaran\n"
            . "3. Hubungi Admin";
    }

    /**
     * @return array<int, array{code: string, label: string}>
     */
    public function getMainMenuOptions(): array
    {
        return [
            ['code' => '1', 'label' => '1. Informasi Kampus'],
            ['code' => '2', 'label' => '2. Informasi Pembayaran'],
            ['code' => '3', 'label' => '3. Hubungi Admin'],
        ];
    }

    /**
     * Tentukan daftar options yang sedang aktif berdasarkan state percakapan dan riwayat pesan.
     * Menggunakan reorder('id', 'desc') agar tidak terjebak default orderBy('id') ASC pada relasi messages().
     *
     * @return array<int, array{code: string, label: string}>
     */
    public function resolveCurrentOptions(Conversation $conversation): array
    {
        if ($conversation->status === Conversation::STATUS_WAITING_ADMIN) {
            return [
                ['code' => '0', 'label' => '0. Keluar / Menu Utama'],
            ];
        }

        if ($conversation->status === Conversation::STATUS_ADMIN_ACTIVE) {
            return [];
        }

        $context = $this->detectActiveMenuContext($conversation);

        if ($context === 'campus') {
            return $this->getCampusCategoryOptions();
        }

        if ($context === 'payment') {
            return $this->getPaymentSubMenuOptions();
        }

        return $this->getMainMenuOptions();
    }

    /**
     * Telusuri pesan dari yang terbaru (DESC) untuk mengetahui sub-menu aktif ('main', 'campus', 'payment').
     * Sub-menu tetap aktif sampai user mengetik/memilih 0 / keluar / menu.
     */
    public function detectActiveMenuContext(Conversation $conversation, ?int $excludeMessageId = null): string
    {
        $query = $conversation->messages()->reorder('id', 'desc');

        if ($excludeMessageId !== null) {
            $query->where('id', '<', $excludeMessageId);
        }

        $recentMessages = $query->limit(30)->get();

        foreach ($recentMessages as $msg) {
            // Jika ada pesan sistem penutupan sesi oleh admin, percakapan di-reset ke main menu
            if ($msg->sender_type === 'system' && str_contains(mb_strtolower($msg->message), 'diakhiri')) {
                return 'main';
            }

            if ($msg->sender_type !== 'user') {
                continue;
            }

            $input = trim(mb_strtolower($msg->message));

            if ($this->isExitToMainMenuCommand($input)) {
                return 'main';
            }

            if ($input === '2' ||
                isset(self::PAYMENT_SUBMENU_MAP[$input]) ||
                str_starts_with($input, '2.') ||
                in_array($input, ['informasi pembayaran', 'pembayaran', 'bayar', 'tagihan', 'ukt', 'jadwal pembayaran', 'saluran pembayaran', 'kendala pembayaran'], true)) {
                return 'payment';
            }

            if ($input === '1' ||
                isset(self::CATEGORY_MENU_MAP[$input]) ||
                str_starts_with($input, '1.') ||
                $this->matchCategoryByKeyword($input) !== null ||
                in_array($input, ['informasi kampus', 'kampus'], true)) {
                return 'campus';
            }

            if (preg_match('/^(?:info|detail)\s*(\d+)$/', $input, $matches)) {
                $info = CampusInformation::published()->find((int) $matches[1]);
                if ($info && $info->category === 'pembayaran') {
                    return 'payment';
                }
                return 'campus';
            }
        }

        return 'main';
    }

    /**
     * Opsi sub-layanan untuk Menu 1 (Informasi Kampus).
     *
     * @return array<int, array{code: string, label: string}>
     */
    public function getCampusCategoryOptions(): array
    {
        return [
            ['code' => '1.1', 'label' => '1.1 Akademik'],
            ['code' => '1.2', 'label' => '1.2 Kalender Akademik'],
            ['code' => '1.3', 'label' => '1.3 Jadwal & Ujian'],
            ['code' => '1.4', 'label' => '1.4 Pengumuman'],
            ['code' => '1.5', 'label' => '1.5 Kegiatan Kampus'],
            ['code' => '1.6', 'label' => '1.6 Administrasi'],
            ['code' => '0', 'label' => '0. Keluar / Menu Utama'],
        ];
    }

    /**
     * Opsi sub-layanan untuk Menu 2 (Informasi Pembayaran).
     *
     * @return array<int, array{code: string, label: string}>
     */
    public function getPaymentSubMenuOptions(): array
    {
        return [
            ['code' => '2.1', 'label' => '2.1 Jadwal & Ketentuan'],
            ['code' => '2.2', 'label' => '2.2 Saluran & VA Bank'],
            ['code' => '2.3', 'label' => '2.3 Kendala Pembayaran'],
            ['code' => '0', 'label' => '0. Keluar / Menu Utama'],
        ];
    }

    protected function buildCampusCategoryOverviewText(): string
    {
        $items = CampusInformation::published()
            ->where('category', '!=', 'pembayaran')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $lines = [
            "*1. INFORMASI KAMPUS UNPAM*",
            "Pilih sub-kategori informasi kampus di bawah ini:",
            "1.1 Akademik",
            "1.2 Kalender Akademik",
            "1.3 Jadwal Perkuliahan & Ujian",
            "1.4 Pengumuman Kampus",
            "1.5 Kegiatan & Agenda",
            "1.6 Administrasi Layanan",
        ];

        if ($items->isNotEmpty()) {
            $lines[] = "\n*Informasi Kampus Terbaru:*";
            foreach ($items as $item) {
                $summary = $item->summary ?: mb_strimwidth(strip_tags($item->content), 0, 110, '...');
                $lines[] = "- *{$item->title}* ({$item->category_label})\n  {$summary}\n  _(Ketik *INFO {$item->id}* untuk detail lengkap)_";
            }
        } else {
            $lines[] = "\nBelum ada informasi kampus yang dipublikasikan saat ini.";
        }

        $lines[] = "\nKetik kode sub-kategori (misal *1.1*) atau tekan tombol. Anda akan tetap di menu ini sampai memilih *0* (Kembali ke Menu Layanan).";

        return implode("\n", $lines);
    }

    protected function buildCategoryItemsText(string $category): string
    {
        $label = CampusInformation::CATEGORIES[$category] ?? ucfirst($category);
        $items = CampusInformation::published()
            ->where('category', $category)
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        if ($items->isEmpty()) {
            return "*Sub-Layanan: {$label}*\nBelum ada informasi yang dipublikasikan pada kategori ini.\n\nPilih sub-kategori lain atau ketik *0* untuk kembali ke Menu Layanan.";
        }

        $lines = ["*Sub-Layanan: {$label}*"];
        foreach ($items as $index => $item) {
            $no = $index + 1;
            $lines[] = "\n{$no}. *{$item->title}*\n{$item->content}";
        }

        $lines[] = "\nSilakan pilih sub-kategori lain pada tombol di bawah, atau tekan *0* untuk kembali ke Menu Layanan.";

        return implode("\n", $lines);
    }

    protected function buildPaymentInformationText(): string
    {
        $items = CampusInformation::published()
            ->where('category', 'pembayaran')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        $lines = [
            "*2. INFORMASI PEMBAYARAN KULIAH*",
            "Informasi resmi jadwal pembayaran, tata cara transaksi, dan administrasi keuangan mahasiswa UNPAM.\n",
            "Pilih sub-layanan informasi pembayaran:",
            "2.1 Jadwal & Ketentuan Pembayaran Kuliah",
            "2.2 Saluran Pembayaran & Nomor Rekening / VA Bank",
            "2.3 Alur Konfirmasi & Penyelesaian Kendala Pembayaran",
        ];

        if ($items->isNotEmpty()) {
            $lines[] = "\n*Pengumuman Pembayaran Terbit:*";
            foreach ($items as $item) {
                $summary = $item->summary ?: mb_strimwidth(strip_tags($item->content), 0, 110, '...');
                $lines[] = "- *{$item->title}*\n  {$summary}\n  _(Ketik *INFO {$item->id}* untuk detail lengkap)_";
            }
        }

        $lines[] = "\nKetik kode sub-layanan (misal *2.1*) atau tekan tombol di bawah. Anda akan tetap di menu ini sampai memilih *0* (Kembali ke Menu Layanan).";

        return implode("\n", $lines);
    }

    protected function buildPaymentSubDetailText(string $subCode): string
    {
        $code = self::PAYMENT_SUBMENU_MAP[$subCode] ?? $subCode;

        if ($code === '2.1' || $code === 'jadwal' || str_contains($subCode, 'jadwal')) {
            $info = CampusInformation::published()
                ->where('category', 'pembayaran')
                ->where(function ($q) {
                    $q->where('slug', 'like', '%jadwal%')
                      ->orWhere('title', 'like', '%jadwal%');
                })
                ->first();

            $content = $info?->content ?? "Jadwal & Ketentuan Pembayaran Kuliah Semester Berjalan:\n1. Pembayaran Registrasi Awal: Tanggal 1 s/d 15 setiap awal semester.\n2. Pembayaran Angsuran UTS: Tanggal 1 s/d 10 sebelum UTS.\n3. Pembayaran Angsuran UAS: Tanggal 1 s/d 10 sebelum UAS.\n- Pastikan melakukan pembayaran sesuai rentang waktu yang telah ditetapkan.";

            return "*2.1 JADWAL & KETENTUAN PEMBAYARAN KULIAH*\n\n"
                . "{$content}\n\n"
                . "Pilih sub-layanan pembayaran lainnya (2.2 / 2.3) atau tekan *0* untuk kembali ke Menu Layanan.";
        }

        if ($code === '2.2' || $code === 'saluran' || str_contains($subCode, 'saluran') || str_contains($subCode, 'bank')) {
            $info = CampusInformation::published()
                ->where('category', 'pembayaran')
                ->where(function ($q) {
                    $q->where('slug', 'like', '%panduan%')
                      ->orWhere('slug', 'like', '%saluran%')
                      ->orWhere('title', 'like', '%saluran%');
                })
                ->first();

            $content = $info?->content ?? "Panduan Saluran Pembayaran Pendidikan UNPAM:\n1. Pembayaran melalui nomor Virtual Account (VA) Bank pada slip tagihan.\n2. Dapat dibayar melalui Teller, ATM, Mobile Banking, atau Internet Banking.\n3. Simpan struk/bukti transaksi pembayaran Anda sebagai arsip pribadi.";

            return "*2.2 PANDUAN SALURAN & NOMOR REKENING / VA BANK*\n\n"
                . "{$content}\n\n"
                . "Pilih sub-layanan pembayaran lainnya (2.1 / 2.3) atau tekan *0* untuk kembali ke Menu Layanan.";
        }

        // 2.3 atau kendala
        return "*2.3 ALUR KONFIRMASI & KENDALA PEMBAYARAN*\n\n"
            . "Jika Anda mengalami kendala pembayaran:\n"
            . "1. Pembayaran berhasil tapi status di sistem belum terverifikasi: Simpan bukti transfer dan sampaikan ke Admin Layanan.\n"
            . "2. Pengajuan dispensasi/penundaan pembayaran karena kondisi khusus: Buat permohonan dispensasi dan koordinasikan dengan bagian keuangan.\n"
            . "3. Anda dapat langsung memilih menu *3. Hubungi Admin* untuk terhubung dengan petugas CRM.\n\n"
            . "Pilih sub-layanan pembayaran lainnya (2.1 / 2.2) atau tekan *0* untuk kembali ke Menu Layanan.";
    }

    protected function buildSingleInformationText(int $id): string
    {
        $item = CampusInformation::published()->find($id);

        if (! $item) {
            return "Informasi dengan kode #{$id} tidak ditemukan atau belum dipublikasikan.\n\nKetik *0* untuk kembali ke Menu Layanan.";
        }

        return "*{$item->title}*\nKategori: {$item->category_label}\n\n{$item->content}\n\nSilakan pilih sub-layanan lain pada tombol di bawah, atau tekan *0* untuk kembali ke Menu Layanan.";
    }

    protected function recordBotReply(Conversation $conversation, string $message): ConversationMessage
    {
        $botMsg = $conversation->messages()->create([
            'sender_type' => 'bot',
            'sender_name' => 'Chatbot Layanan UNPAM',
            'message' => $message,
            'delivery_status' => 'delivered',
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        return $botMsg;
    }
}
