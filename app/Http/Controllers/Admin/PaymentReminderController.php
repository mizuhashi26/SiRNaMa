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
                'target_type' => 'all',
                'status' => 'scheduled',
                'scheduled_at' => now()->addHour()->format('Y-m-d\TH:i'),
            ]),
            'categories' => PaymentReminder::CATEGORIES,
            'targetTypes' => PaymentReminder::TARGET_TYPES,
            'isEdit' => false,
            'activeStudentCount' => Student::where('is_active', true)->count(),
            'students' => Student::where('is_active', true)->orderBy('name')->get(['id', 'nim', 'name', 'whatsapp_number']),
        ]);
    }

    public function store(Request $request, ReminderDispatchService $dispatchService): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'in:' . implode(',', array_keys(PaymentReminder::CATEGORIES))],
            'target_type' => ['nullable', 'string', 'in:' . implode(',', array_keys(PaymentReminder::TARGET_TYPES))],
            'personal_student_id' => ['nullable', 'exists:students,id'],
            'selected_student_ids' => ['nullable', 'array'],
            'selected_student_ids.*' => ['exists:students,id'],
            'custom_recipients' => ['nullable', 'string', 'max:2000'],
            'message' => ['required', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
        ], [
            'title.required' => 'Judul reminder wajib diisi.',
            'category.required' => 'Kategori reminder wajib dipilih.',
            'message.required' => 'Isi pesan reminder wajib diisi.',
        ]);

        $targetType = $validated['target_type'] ?? 'all';
        $studentIds = match($targetType) {
            'personal' => !empty($validated['personal_student_id']) ? [(int) $validated['personal_student_id']] : null,
            'selected' => !empty($validated['selected_student_ids']) ? array_map('intval', $validated['selected_student_ids']) : null,
            default => null,
        };

        $customRecipients = match($targetType) {
            'personal' => $request->input('custom_recipients_personal') ?? $validated['custom_recipients'] ?? null,
            'selected' => $request->input('custom_recipients_bulk') ?? $validated['custom_recipients'] ?? null,
            default => null,
        };

        $reminder = new PaymentReminder([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'target_type' => $targetType,
            'target_student_ids' => $studentIds,
            'custom_recipients' => $customRecipients,
            'message' => $validated['message'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => ! empty($validated['scheduled_at']) ? 'scheduled' : 'draft',
        ]);

        $targets = $dispatchService->resolveRecipients($reminder);
        $reminder->total_targets = count($targets);
        $reminder->save();

        if ($request->input('action_submit') === 'send_now') {
            $result = $dispatchService->dispatchReminder($reminder);
            $flashType = $result['status'] === 'sent' ? 'success' : 'warning';
            return redirect()->route('admin.reminders.show', $reminder)
                ->with($flashType, $result['notice']);
        }

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
            'targetTypes' => PaymentReminder::TARGET_TYPES,
            'isEdit' => true,
            'activeStudentCount' => Student::where('is_active', true)->count(),
            'students' => Student::where('is_active', true)->orderBy('name')->get(['id', 'nim', 'name', 'whatsapp_number']),
        ]);
    }

    public function update(Request $request, PaymentReminder $reminder, ReminderDispatchService $dispatchService): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'in:' . implode(',', array_keys(PaymentReminder::CATEGORIES))],
            'target_type' => ['nullable', 'string', 'in:' . implode(',', array_keys(PaymentReminder::TARGET_TYPES))],
            'personal_student_id' => ['nullable', 'exists:students,id'],
            'selected_student_ids' => ['nullable', 'array'],
            'selected_student_ids.*' => ['exists:students,id'],
            'custom_recipients' => ['nullable', 'string', 'max:2000'],
            'message' => ['required', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['nullable', 'in:draft,scheduled'],
        ]);

        $status = $validated['status'] ?? (! empty($validated['scheduled_at']) ? 'scheduled' : 'draft');
        $targetType = $validated['target_type'] ?? 'all';
        $studentIds = match($targetType) {
            'personal' => !empty($validated['personal_student_id']) ? [(int) $validated['personal_student_id']] : null,
            'selected' => !empty($validated['selected_student_ids']) ? array_map('intval', $validated['selected_student_ids']) : null,
            default => null,
        };

        $customRecipients = match($targetType) {
            'personal' => $request->input('custom_recipients_personal') ?? $validated['custom_recipients'] ?? null,
            'selected' => $request->input('custom_recipients_bulk') ?? $validated['custom_recipients'] ?? null,
            default => null,
        };

        $reminder->fill([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'target_type' => $targetType,
            'target_student_ids' => $studentIds,
            'custom_recipients' => $customRecipients,
            'message' => $validated['message'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $status,
        ]);

        $targets = $dispatchService->resolveRecipients($reminder);
        $reminder->total_targets = count($targets);
        $reminder->save();

        if ($request->input('action_submit') === 'send_now') {
            $result = $dispatchService->dispatchReminder($reminder);
            $flashType = $result['status'] === 'sent' ? 'success' : 'warning';
            return redirect()->route('admin.reminders.show', $reminder)
                ->with($flashType, $result['notice']);
        }

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

