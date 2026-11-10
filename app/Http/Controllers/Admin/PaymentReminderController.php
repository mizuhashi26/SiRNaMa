<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentReminder;
use App\Models\Student;
use App\Services\FonnteService;
use App\Services\ReminderDispatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentReminderController extends Controller
{
    public function index(FonnteService $fonnteService, ReminderDispatchService $dispatchService): View
    {
        // Jalankan auto-dispatch untuk reminder yang jadwalnya sudah tiba
        $dueReminders = PaymentReminder::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($dueReminders as $due) {
            $dispatchService->dispatchReminder($due);
        }

        $reminders = PaymentReminder::withCount('logs')
            ->orderByDesc('created_at')
            ->paginate(10);

        $activeStudentCount = Student::where('is_active', true)->count();

        return view('admin.reminders.index', [
            'reminders' => $reminders,
            'activeStudentCount' => $activeStudentCount,
            'fonnteConfigured' => $fonnteService->isConfigured(),
        ]);
    }

    public function create(): View
    {
        return view('admin.reminders.form', [
            'reminder' => new PaymentReminder([
                'category' => 'pembayaran',
                'status' => 'scheduled',
                'scheduled_at' => now()->addHour()->format('Y-m-d\TH:i'),
            ]),
            'categories' => PaymentReminder::CATEGORIES,
            'isEdit' => false,
            'activeStudentCount' => Student::where('is_active', true)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'in:' . implode(',', array_keys(PaymentReminder::CATEGORIES))],
            'message' => ['required', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
        ], [
            'title.required' => 'Judul reminder wajib diisi.',
            'category.required' => 'Kategori reminder wajib dipilih.',
            'message.required' => 'Isi pesan reminder wajib diisi.',
        ]);

        PaymentReminder::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'message' => $validated['message'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => ! empty($validated['scheduled_at']) ? 'scheduled' : 'draft',
            'total_targets' => Student::where('is_active', true)->count(),
        ]);

        return redirect()->route('admin.reminders.index')
            ->with('success', 'Jadwal reminder mahasiswa berhasil disimpan.');
    }

    public function show(PaymentReminder $reminder): View
    {
        $logs = $reminder->logs()->latest('id')->paginate(20);

        return view('admin.reminders.show', [
            'reminder' => $reminder,
            'logs' => $logs,
        ]);
    }

    public function edit(PaymentReminder $reminder): View
    {
        return view('admin.reminders.form', [
            'reminder' => $reminder,
            'categories' => PaymentReminder::CATEGORIES,
            'isEdit' => true,
            'activeStudentCount' => Student::where('is_active', true)->count(),
        ]);
    }

    public function update(Request $request, PaymentReminder $reminder): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'in:' . implode(',', array_keys(PaymentReminder::CATEGORIES))],
            'message' => ['required', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['nullable', 'in:draft,scheduled'],
        ]);

        $status = $validated['status'] ?? (! empty($validated['scheduled_at']) ? 'scheduled' : 'draft');

        $reminder->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'message' => $validated['message'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $status,
        ]);

        return redirect()->route('admin.reminders.index')
            ->with('success', 'Data reminder mahasiswa berhasil diperbarui.');
    }

    public function destroy(PaymentReminder $reminder): RedirectResponse
    {
        $reminder->delete();

        return redirect()->route('admin.reminders.index')
            ->with('success', 'Data reminder mahasiswa berhasil dihapus.');
    }

    public function sendNow(PaymentReminder $reminder, ReminderDispatchService $dispatchService): RedirectResponse
    {
        $result = $dispatchService->dispatchReminder($reminder);

        $flashType = $result['status'] === 'sent' ? 'success' : 'warning';

        return redirect()->route('admin.reminders.show', $reminder)
            ->with($flashType, $result['notice']);
    }
}

