<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Throwable;

class FonnteService
{
    public function isConfigured(): bool
    {
        return filled(config('services.fonnte.token'));
    }

    /**
     * Kirim pesan WhatsApp melalui API Fonnte.
     *
     * @return array{success: bool, status: string, message: string, response: mixed}
     */
    public function sendMessage(string $targetPhone, string $message): array
    {
        $normalizedPhone = Student::normalizePhone($targetPhone);

        if ($normalizedPhone === '') {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Nomor WhatsApp tujuan tidak valid.',
                'response' => null,
            ];
        }

        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'status' => 'unverified_no_token',
                'message' => 'FONNTE_TOKEN belum diisi pada file .env. Pengiriman WhatsApp belum dapat diuji ke gateway Fonnte.',
                'response' => [
                    'simulated_target' => $normalizedPhone,
                    'note' => 'Belum terverifikasi karena FONNTE_TOKEN kosong.',
                ],
            ];
        }

        $apiUrl = (string) config('services.fonnte.api_url', 'https://api.fonnte.com/send');
        $token = (string) config('services.fonnte.token');

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])
                ->timeout(15)
                ->asForm()
                ->post($apiUrl, [
                    'target' => $normalizedPhone,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            $payload = $response->json();
            $apiSuccess = $response->successful() && is_array($payload) && (($payload['status'] ?? false) === true);

            if ($apiSuccess) {
                return [
                    'success' => true,
                    'status' => 'sent',
                    'message' => 'Pesan berhasil dikirim melalui API Fonnte.',
                    'response' => $payload,
                ];
            }

            $reason = is_array($payload)
                ? (string) ($payload['reason'] ?? $payload['message'] ?? 'Respons Fonnte menyatakan pengiriman gagal.')
                : 'HTTP ' . $response->status();

            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Gagal mengirim melalui Fonnte: ' . $reason,
                'response' => $payload ?? $response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Koneksi ke API Fonnte gagal: ' . $e->getMessage(),
                'response' => ['exception' => $e->getMessage()],
            ];
        }
    }
}

