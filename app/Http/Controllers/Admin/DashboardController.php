<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampusInformation;
use App\Models\Conversation;
use App\Models\PaymentReminder;
use App\Models\Student;
use App\Services\FonnteService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(FonnteService $fonnteService): View
    {
        $waitingConversations = Conversation::with('assignedAdmin')
            ->whereIn('status', [Conversation::STATUS_WAITING_ADMIN, Conversation::STATUS_ADMIN_ACTIVE])
            ->orderByRaw("CASE WHEN status = 'WAITING_ADMIN' THEN 0 ELSE 1 END")
            ->orderByDesc('last_message_at')
            ->limit(8)
            ->get();

        $upcomingReminders = PaymentReminder::orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentInformations = CampusInformation::orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $counts = [
            'waiting_admin' => Conversation::where('status', Conversation::STATUS_WAITING_ADMIN)->count(),
            'admin_active' => Conversation::where('status', Conversation::STATUS_ADMIN_ACTIVE)->count(),
            'published_info' => CampusInformation::where('is_published', true)->count(),
            'active_students' => Student::where('is_active', true)->count(),
            'scheduled_reminders' => PaymentReminder::where('status', 'scheduled')->count(),
        ];

        return view('admin.dashboard', [
            'waitingConversations' => $waitingConversations,
            'upcomingReminders' => $upcomingReminders,
            'recentInformations' => $recentInformations,
            'counts' => $counts,
            'fonnteConfigured' => $fonnteService->isConfigured(),
        ]);
    }
}

