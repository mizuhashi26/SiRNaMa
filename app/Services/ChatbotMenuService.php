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

        // Jika percakapan sebelumnya sudah CLOSED, interaksi baru membuka kembali sesi BOT_ACTIVE
        // kecuali pengguna langsung memilih 3 (Hubungi Admin) yang akan diproses di bawah.
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

        // Jika sedang WAITING_ADMIN, izinkan kembali ke menu bot jika ketik 0 / menu / batal.
        if ($conversation->status === Conversation::STATUS_WAITING_ADMIN) {
            if (in_array($normalized, ['0', 'menu', 'batal'], true)) {
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

            // Jika mahasiswa memilih ulang tombol 3 saat sudah WAITING_ADMIN, beri konfirmasi singkat.
            if (in_array($normalized, ['3', 'hubungi admin', 'admin'], true)) {
                $replyText = 'Percakapan Anda sudah berada dalam antrean Menunggu Admin. Silakan tulis pesan atau pertanyaan Anda, Admin akan segera mengambil alih.';
                $botMessage = $this->recordBotReply($conversation, $replyText);

                return [
                    'conversation' => $conversation->fresh(),
                    'user_message' => $userMessage,
                    'bot_message' => $botMessage,
                    'options' => [
                        ['code' => '0', 'label' => '0. Kembali ke Menu Bot'],
                    ],
                ];
            }

            // Pesan teks bebas dari mahasiswa saat WAITING_ADMIN disimpan untuk dibaca admin tanpa ditimpa menu bot.
            return [
                'conversation' => $conversation->fresh(),
                'user_message' => $userMessage,
                'bot_message' => null,
                'options' => [
                    ['code' => '0', 'label' => '0. Kembali ke Menu Bot'],
                ],
            ];
        }

        // Status BOT_ACTIVE: proses pilihan menu / keyword
        $resolved = $this->resolveBotResponse($conversation, $normalized);

        $botMessage = $this->recordBotReply($conversation, $resolved['text']);

        return [
            'conversation' => $conversation->fresh(),
            'user_message' => $userMessage,
            'bot_message' => $botMessage,
            'options' => $resolved['options'],
        ];
    }

    /**
     * @return array{text: string, options: array<int, array{code: string, label: string}>}
     */
    protected function resolveBotResponse(Conversation $conversation, string $normalized): array
    {
        // Menu 3: Hubungi Admin
        if (in_array($normalized, ['3', 'hubungi admin', 'admin', 'cs', 'bantuan'], true)) {
            $conversation->status = Conversation::STATUS_WAITING_ADMIN;
            $conversation->save();

            return [
                'text' => "Permintaan Anda telah masuk ke antrean dashboard CRM dengan status *Menunggu Admin*.\n\nSilakan ketik pertanyaan atau kendala Anda di bawah ini. Admin akan segera mengambil alih percakapan.\n(Ketik *0* jika ingin membatalkan dan kembali ke menu bot).",
                'options' => [
                    ['code' => '0', 'label' => '0. Kembali ke Menu Bot'],
                ],
            ];
        }

        // Menu 2: Informasi Pembayaran
        if (in_array($normalized, ['2', 'informasi pembayaran', 'pembayaran', 'bayar', 'tagihan', 'ukt'], true)) {
            return [
                'text' => $this->buildPaymentInformationText(),
                'options' => $this->getMainMenuOptions(),
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
                'options' => $this->getCampusCategoryOptions(),
            ];
        }

        // Detail item spesifik: format "info <id>" atau "detail <id>"
        if (preg_match('/^(?:info|detail)\s*(\d+)$/', $normalized, $matches)) {
            $infoId = (int) $matches[1];

            return [
                'text' => $this->buildSingleInformationText($infoId),
                'options' => $this->getMainMenuOptions(),
            ];
        }

        // Default / Menu Utama (0, menu, halo, mulai, dll.)
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
        return "Halo! Selamat datang di Layanan Informasi Mahasiswa UNPAM *(Simulasi Akademik CRM)*.\n\n"
            . "Silakan pilih menu layanan dengan mengetik angka atau menekan tombol berikut:\n"
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
            ['code' => '0', 'label' => '0. Menu Utama'],
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
            "*1. INFORMASI KAMPUS (SIMULASI UNPAM)*",
            "Pilih sub-kategori informasi:",
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

        $lines[] = "\nKetik kode sub-kategori (misal *1.1*) atau *0* untuk kembali ke Menu Utama.";

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
            return "*Kategori: {$label}*\nBelum ada informasi yang dipublikasikan pada kategori ini.\n\nKetik *1* untuk kategori lain atau *0* untuk Menu Utama.";
        }

        $lines = ["*Kategori: {$label} (Simulasi)*"];
        foreach ($items as $index => $item) {
            $no = $index + 1;
            $lines[] = "\n{$no}. *{$item->title}*\n{$item->content}";
        }

        $lines[] = "\nKetik *1* untuk sub-kategori lain, *2* untuk Informasi Pembayaran, *3* untuk Hubungi Admin, atau *0* untuk Menu Utama.";

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
            "*2. INFORMASI PEMBAYARAN KULIAH UMUM (SIMULASI)*",
            "_(Catatan: Informasi ini bersifat umum untuk seluruh mahasiswa pada simulasi akademik, bukan tagihan individual.)_",
        ];

        if ($items->isEmpty()) {
            $lines[] = "\nSaat ini belum ada pengumuman jadwal pembayaran kuliah umum yang dipublikasikan.";
        } else {
            foreach ($items as $index => $item) {
                $no = $index + 1;
                $lines[] = "\n{$no}. *{$item->title}*\n{$item->content}";
            }
        }

        $lines[] = "\nKetik *1* untuk Informasi Kampus, *3* untuk Hubungi Admin, atau *0* untuk Menu Utama.";

        return implode("\n", $lines);
    }

    protected function buildSingleInformationText(int $id): string
    {
        $item = CampusInformation::published()->find($id);

        if (! $item) {
            return "Informasi dengan kode #{$id} tidak ditemukan atau belum dipublikasikan.\n\nKetik *1* untuk daftar Informasi Kampus atau *0* untuk Menu Utama.";
        }

        return "*{$item->title}*\nKategori: {$item->category_label} _(Simulasi)_\n\n{$item->content}\n\nKetik *1* untuk Informasi Kampus, *2* untuk Informasi Pembayaran, atau *3* untuk Hubungi Admin.";
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

