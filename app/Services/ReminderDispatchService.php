<?php

namespace App\Services;

use App\Models\PaymentReminder;
use App\Models\Student;

class ReminderDispatchService
{
    public function __construct(
        protected FonnteService $fonnteService
    ) {}

    /**
     * Kirim reminder pembayaran umum ke seluruh mahasiswa dummy aktif.
     *
     * @return array{
     *   status: string,
     *   total_targets: int,
     *   sent_count: int,
     *   failed_count: int,
     *   notice: string
     * }
     */
    public function dispatchReminder(PaymentReminder $reminder): array
    {
        $students = Student::where('is_active', true)->orderBy('id')->get();
        $totalTargets = $students->count();

        if ($totalTargets === 0) {
            $reminder->update([
                'status' => 'failed',
                'total_targets' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'last_error' => 'Tidak ada mahasiswa aktif pada daftar simulasi.',
            ]);

            return [
                'status' => 'failed',
                'total_targets' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'notice' => 'Pengiriman dibatalkan karena belum ada mahasiswa aktif terdaftar.',
            ];
        }

        $sentCount = 0;
        $failedCount = 0;
        $lastError = null;
        $unverifiedNoToken = false;

        foreach ($students as $student) {
            $formattedMessage = "[SIMULASI AKADEMIK UNPAM - REMINDER PEMBAYARAN UMUM]\n"
                . "Yth. {$student->name} ({$student->nim}),\n\n"
                . "*{$reminder->title}*\n"
                . "{$reminder->message}\n\n"
                . "_Pesan ini adalah simulasi sistem CRM tugas kuliah, bukan tagihan resmi UNPAM._";

            $result = $this->fonnteService->sendMessage($student->whatsapp_number, $formattedMessage);

            if ($result['success']) {
                $sentCount++;
            } else {
                $failedCount++;
                $lastError = $result['message'];
                if ($result['status'] === 'unverified_no_token') {
                    $unverifiedNoToken = true;
                }
            }

            $reminder->logs()->create([
                'student_id' => $student->id,
                'recipient_name' => $student->name,
                'whatsapp_number' => $student->whatsapp_number,
                'status' => $result['status'],
                'provider_response' => is_string($result['response'])
                    ? $result['response']
                    : json_encode($result['response'], JSON_UNESCAPED_UNICODE),
                'sent_at' => now(),
            ]);
        }

        if ($sentCount === $totalTargets) {
            $finalStatus = 'sent';
            $notice = "Reminder berhasil dikirim via Fonnte ke {$sentCount} nomor WhatsApp mahasiswa.";
        } elseif ($sentCount > 0) {
            $finalStatus = 'partial';
            $notice = "Reminder terkirim sebagian: {$sentCount} berhasil, {$failedCount} gagal.";
        } elseif ($unverifiedNoToken) {
            $finalStatus = 'unverified_no_token';
            $notice = "Belum diuji ke WhatsApp: FONNTE_TOKEN pada .env belum diisi ({$totalTargets} log dicatat sebagai belum terverifikasi).";
        } else {
            $finalStatus = 'failed';
            $notice = "Pengiriman gagal untuk {$failedCount} nomor. Pesan error terakhir: {$lastError}";
        }

        $reminder->update([
            'status' => $finalStatus,
            'sent_at' => now(),
            'total_targets' => $totalTargets,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'last_error' => $lastError,
        ]);

        return [
            'status' => $finalStatus,
            'total_targets' => $totalTargets,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'notice' => $notice,
        ];
    }
}

