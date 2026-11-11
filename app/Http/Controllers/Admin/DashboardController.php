<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use App\Models\Conversation;
use App\Models\PaymentReminder;
use App\Models\Student;
use App\Services\FonnteService;
use App\Services\ReminderDispatchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, FonnteService $fonnteService, ReminderDispatchService $dispatchService): View
    {
        // Jalankan pengiriman otomatis untuk reminder yang jatuh tempo
        $dueReminders = PaymentReminder::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($dueReminders as $due) {
            $dispatchService->dispatchReminder($due);
        }

        $tab = (string) $request->query('tab', 'all');

        $conversationsQuery = Conversation::with(['assignedAdmin', 'messages']);

        if ($tab === 'waiting') {
            $conversationsQuery->where('status', Conversation::STATUS_WAITING_ADMIN);
        } elseif ($tab === 'admin') {
            $conversationsQuery->where('status', Conversation::STATUS_ADMIN_ACTIVE);
        } elseif ($tab === 'bot') {
            $conversationsQuery->where('status', Conversation::STATUS_BOT_ACTIVE);
        } else {
            // 'all': Tampilkan semua percakapan yang sedang aktif (Menunggu Admin, Admin Aktif, atau Bot Aktif)
            $conversationsQuery->whereIn('status', [
                Conversation::STATUS_WAITING_ADMIN,
                Conversation::STATUS_ADMIN_ACTIVE,
                Conversation::STATUS_BOT_ACTIVE,
            ]);
        }

        $conversations = $conversationsQuery
            ->orderByRaw("CASE
                WHEN status = 'WAITING_ADMIN' THEN 0
                WHEN status = 'ADMIN_ACTIVE' THEN 1
                WHEN status = 'BOT_ACTIVE' THEN 2
                ELSE 3
            END")
            ->orderByDesc('last_message_at')
            ->limit(10)
            ->get();

        // Cari tahu nomor WhatsApp yang sudah terdaftar di tabel mahasiswa
        $contactNumbers = $conversations->pluck('contact_number')->filter()->unique()->toArray();
        $registeredPhones = !empty($contactNumbers)
            ? Student::whereIn('whatsapp_number', $contactNumbers)->pluck('whatsapp_number')->flip()->toArray()
            : [];

        $upcomingReminders = PaymentReminder::orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentInformations = CampusInformation::orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $counts = [
            'waiting_admin' => Conversation::where('status', Conversation::STATUS_WAITING_ADMIN)->count(),
            'admin_active' => Conversation::where('status', Conversation::STATUS_ADMIN_ACTIVE)->count(),
            'bot_active' => Conversation::where('status', Conversation::STATUS_BOT_ACTIVE)->count(),
            'total_active' => Conversation::whereIn('status', [
                Conversation::STATUS_WAITING_ADMIN,
                Conversation::STATUS_ADMIN_ACTIVE,
                Conversation::STATUS_BOT_ACTIVE,
            ])->count(),
            'published_info' => CampusInformation::where('is_published', true)->count(),
            'active_students' => Student::where('is_active', true)->count(),
            'scheduled_reminders' => PaymentReminder::where('status', 'scheduled')->count(),
        ];

        return view('admin.dashboard', [
            'conversations' => $conversations,
            'waitingConversations' => $conversations,
            'currentTab' => $tab,
            'registeredPhones' => $registeredPhones,
            'upcomingReminders' => $upcomingReminders,
            'recentInformations' => $recentInformations,
            'counts' => $counts,
            'fonnteConfigured' => $fonnteService->isConfigured(),
        ]);
    }
}

