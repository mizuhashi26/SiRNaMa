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
     * Kirim reminder pengumuman/pembayaran ke target penerima (seluruh mahasiswa, personal, atau nomor pilihan).
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
        $recipients = $this->resolveRecipients($reminder);
        $totalTargets = count($recipients);

        if ($totalTargets === 0) {
            $reminder->update([
                'status' => 'failed',
                'total_targets' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'last_error' => 'Tidak ada target penerima yang valid.',
            ]);

            return [
                'status' => 'failed',
                'total_targets' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
                'notice' => 'Pengiriman dibatalkan karena tidak ada target penerima yang valid.',
            ];
        }

        $sentCount = 0;
        $failedCount = 0;
        $lastError = null;
        $unverifiedNoToken = false;

        foreach ($recipients as $recipient) {
            $studentGreeting = !empty($recipient['nim'])
                ? "Yth. {$recipient['name']} ({$recipient['nim']}),\n\n"
                : "Yth. {$recipient['name']},\n\n";

            $formattedMessage = $studentGreeting
                . "*{$reminder->title}*\n"
                . "{$reminder->message}\n\n"
                . "Layanan Informasi & Kemahasiswaan Universitas Pamulang";

            $result = $this->fonnteService->sendMessage($recipient['whatsapp_number'], $formattedMessage);

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
                'student_id' => $recipient['student_id'],
                'recipient_name' => $recipient['name'],
                'whatsapp_number' => $recipient['whatsapp_number'],
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

    /**
     * Uraikan target penerima berdasarkan target_type (all, personal, selected).
     *
     * @return array<int, array{student_id: ?int, name: string, nim: ?string, whatsapp_number: string}>
     */
    public function resolveRecipients(PaymentReminder $reminder): array
    {
        $targetType = $reminder->target_type ?? 'all';
        $recipients = [];
        $seenNumbers = [];

        if ($targetType === 'personal') {
            // 1. Jika memilih mahasiswa terdaftar
            if (!empty($reminder->target_student_ids)) {
                $studentId = is_array($reminder->target_student_ids) ? $reminder->target_student_ids[0] : (int) $reminder->target_student_ids;
                $student = Student::find($studentId);
                if ($student) {
                    $recipients[] = [
                        'student_id' => $student->id,
                        'name' => $student->name,
                        'nim' => $student->nim,
                        'whatsapp_number' => $student->whatsapp_number,
                    ];
                    $seenNumbers[$student->whatsapp_number] = true;
                }
            }

            // 2. Dan/atau jika memasukkan nomor manual
            if (!empty($reminder->custom_recipients)) {
                $raw = trim($reminder->custom_recipients);
                $parts = explode(':', $raw, 2);
                $name = count($parts) === 2 ? trim($parts[0]) : 'Penerima Pengingat';
                $phone = count($parts) === 2 ? trim($parts[1]) : $raw;
                $normalized = Student::normalizePhone($phone);
                if (strlen($normalized) >= 9 && empty($seenNumbers[$normalized])) {
                    $recipients[] = [
                        'student_id' => null,
                        'name' => $name,
                        'nim' => null,
                        'whatsapp_number' => $normalized,
                    ];
                    $seenNumbers[$normalized] = true;
                }
            }
        } elseif ($targetType === 'selected') {
            // 1. Dari daftar mahasiswa yang dicentang
            if (!empty($reminder->target_student_ids) && is_array($reminder->target_student_ids)) {
                $students = Student::whereIn('id', $reminder->target_student_ids)->orderBy('name')->get();
                foreach ($students as $s) {
                    if (!isset($seenNumbers[$s->whatsapp_number])) {
                        $recipients[] = [
                            'student_id' => $s->id,
                            'name' => $s->name,
                            'nim' => $s->nim,
                            'whatsapp_number' => $s->whatsapp_number,
                        ];
                        $seenNumbers[$s->whatsapp_number] = true;
                    }
                }
            }

            // 2. Dari nomor tambahan manual (pisahkan baris / koma)
            if (!empty($reminder->custom_recipients)) {
                $lines = preg_split('/[\r\n,]+/', $reminder->custom_recipients);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') continue;

                    $parts = explode(':', $line, 2);
                    $name = count($parts) === 2 ? trim($parts[0]) : 'Mahasiswa / Kontak';
                    $phone = count($parts) === 2 ? trim($parts[1]) : $line;
                    $normalized = Student::normalizePhone($phone);

                    if (strlen($normalized) >= 9 && !isset($seenNumbers[$normalized])) {
                        $recipients[] = [
                            'student_id' => null,
                            'name' => $name,
                            'nim' => null,
                            'whatsapp_number' => $normalized,
                        ];
                        $seenNumbers[$normalized] = true;
                    }
                }
            }
        } else {
            // 'all': Seluruh mahasiswa aktif
            $students = Student::where('is_active', true)->orderBy('id')->get();
            foreach ($students as $s) {
                $recipients[] = [
                    'student_id' => $s->id,
                    'name' => $s->name,
                    'nim' => $s->nim,
                    'whatsapp_number' => $s->whatsapp_number,
                ];
            }
        }

        return $recipients;
    }
}

