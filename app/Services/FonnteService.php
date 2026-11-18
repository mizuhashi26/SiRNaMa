<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Student;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class FonnteService
{
    public const DEFAULT_MIN_DELAY_SECONDS = 2;

    public const DEFAULT_MAX_DELAY_SECONDS = 8;

    public const MAX_BATCH_SIZE = 50;
    public function getToken(): string
    {
        try {
            return (string) (AppSetting::getValue('fonnte_token') ?: config('services.fonnte.token'));
        } catch (Throwable) {
            return (string) config('services.fonnte.token');
        }
    }

    public function getApiUrl(): string
    {
        try {
            return (string) (AppSetting::getValue('fonnte_api_url') ?: config('services.fonnte.api_url', 'https://api.fonnte.com/send'));
        } catch (Throwable) {
            return (string) config('services.fonnte.api_url', 'https://api.fonnte.com/send');
        }
    }

    public function isConfigured(): bool
    {
        return filled($this->getToken());
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
                'message' => 'FONNTE_TOKEN belum diisi di Pengaturan atau file .env. Pengiriman WhatsApp belum dapat diuji ke gateway Fonnte.',
                'response' => [
                    'simulated_target' => $normalizedPhone,
                    'note' => 'Belum terverifikasi karena Token Fonnte kosong.',
                ],
            ];
        }

        $apiUrl = $this->getApiUrl();
        $token = $this->getToken();

        try {
            $response = $this->newHttpClient($token)
                ->post($apiUrl, [
                    'target' => $normalizedPhone,
                    'message' => $message,
                    'countryCode' => '62',
                ]);

            return $this->parseGatewayResponse($response);
        } catch (Throwable $e) {
            return $this->exceptionResult($e);
        }
    }

    /**
     * Kirim pesan massal ke banyak kontak melalui parameter `data` Fonnte
     * dengan jeda waktu acak unik antar kontak (tidak pernah seragam) untuk mencegah blokir WhatsApp.
     *
     * @param  array<int, array{target: string, message: string, delay?: string|null}>  $messages
     * @return array<int, array{success: bool, status: string, message: string, delay: string, response: mixed}>
     */
    public function sendBulkMessages(array $messages): array
    {
        if (empty($messages)) {
            return [];
        }

        $results = [];
        $validQueue = [];

        foreach ($messages as $idx => $item) {
            $normalizedPhone = Student::normalizePhone((string) ($item['target'] ?? ''));

            if ($normalizedPhone === '') {
                $results[$idx] = [
                    'success' => false,
                    'status' => 'failed',
                    'message' => 'Nomor WhatsApp tujuan tidak valid.',
                    'delay' => '0',
                    'response' => null,
                ];
                continue;
            }

            $validQueue[] = [
                'index' => $idx,
                'target' => $normalizedPhone,
                'message' => (string) ($item['message'] ?? ''),
                'delay' => isset($item['delay']) && is_string($item['delay']) && trim($item['delay']) !== ''
                    ? trim($item['delay'])
                    : null,
            ];
        }

        if (empty($validQueue)) {
            ksort($results);
            return $results;
        }

        $delays = $this->generateRandomDelays(count($validQueue));

        if (! $this->isConfigured()) {
            foreach ($validQueue as $pos => $entry) {
                $delayStr = $entry['delay'] ?? $delays[$pos];
                $results[$entry['index']] = [
                    'success' => false,
                    'status' => 'unverified_no_token',
                    'message' => 'FONNTE_TOKEN belum diisi di Pengaturan atau file .env. Pengiriman WhatsApp belum dapat diuji ke gateway Fonnte.',
                    'delay' => $delayStr,
                    'response' => [
                        'simulated_target' => $entry['target'],
                        'delay_seconds' => (int) $delayStr,
                        'note' => 'Belum terverifikasi karena Token Fonnte kosong.',
                    ],
                ];
            }

            ksort($results);
            return $results;
        }

        if (count($validQueue) === 1) {
            $single = $validQueue[0];
            $singleResult = $this->sendMessage($single['target'], $single['message']);
            $singleResult['delay'] = '0';
            $results[$single['index']] = $singleResult;

            ksort($results);
            return $results;
        }

        $apiUrl = $this->getApiUrl();
        $token = $this->getToken();
        $baseTimestamp = time();
        $cumulativeOffsetSeconds = 0;

        foreach (array_chunk($validQueue, self::MAX_BATCH_SIZE) as $chunkIndex => $chunk) {
            $batchPayload = [];
            $chunkDelays = [];
            $chunkSchedule = $chunkIndex > 0 ? ($baseTimestamp + $cumulativeOffsetSeconds) : null;

            foreach ($chunk as $offset => $entry) {
                $globalPos = ($chunkIndex * self::MAX_BATCH_SIZE) + $offset;
                $delayStr = $entry['delay'] ?? $delays[$globalPos];
                $chunkDelays[$entry['index']] = $delayStr;
                $cumulativeOffsetSeconds += max(1, (int) $delayStr);

                $payloadItem = [
                    'target' => $entry['target'],
                    'message' => $entry['message'],
                    'countryCode' => '62',
                    'delay' => (string) $delayStr,
                ];

                if ($chunkSchedule !== null) {
                    $payloadItem['schedule'] = $chunkSchedule;
                }

                $batchPayload[] = $payloadItem;
            }

            try {
                $encodedData = json_encode($batchPayload, JSON_UNESCAPED_UNICODE);
                if ($encodedData === false) {
                    throw new \RuntimeException('Gagal menyusun JSON batch pengiriman Fonnte.');
                }

                $response = $this->newHttpClient($token)->post($apiUrl, [
                    'data' => $encodedData,
                ]);

                $gatewayResult = $this->parseGatewayResponse($response);
            } catch (Throwable $e) {
                $gatewayResult = $this->exceptionResult($e);
            }

            foreach ($chunk as $entry) {
                $delayStr = $chunkDelays[$entry['index']];
                $results[$entry['index']] = [
                    'success' => $gatewayResult['success'],
                    'status' => $gatewayResult['status'],
                    'message' => $gatewayResult['message'],
                    'delay' => $delayStr,
                    'response' => [
                        'delay_seconds' => (int) $delayStr,
                        'batch_size' => count($batchPayload),
                        'gateway' => $gatewayResult['response'],
                    ],
                ];
            }
        }

        ksort($results);
        return $results;
    }

    /**
     * Buat daftar jeda acak (detik, tipe string) tanpa angka berurutan yang sama persis.
     *
     * @return array<int, string>
     */
    public function generateRandomDelays(int $count, ?int $minDelay = null, ?int $maxDelay = null): array
    {
        if ($count <= 0) {
            return [];
        }

        $min = max(1, $minDelay ?? (int) config('services.fonnte.min_delay', self::DEFAULT_MIN_DELAY_SECONDS));
        $max = max($min, $maxDelay ?? (int) config('services.fonnte.max_delay', self::DEFAULT_MAX_DELAY_SECONDS));

        $delays = [];
        $previous = null;

        for ($i = 0; $i < $count; $i++) {
            $delay = random_int($min, $max);
            if ($previous !== null && $max > $min) {
                while ($delay === $previous) {
                    $delay = random_int($min, $max);
                }
            }
            $previous = $delay;
            $delays[] = (string) $delay;
        }

        return $delays;
    }

    protected function newHttpClient(string $token): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => $token,
        ])
            ->withOptions([
                'curl' => [
                    CURLOPT_RESOLVE => ['api.fonnte.com:443:103.52.212.50'],
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
            ])
            ->timeout(15)
            ->asForm();
    }

    /**
     * @return array{success: bool, status: string, message: string, response: mixed}
     */
    protected function parseGatewayResponse(Response $response): array
    {
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
    }

    /**
     * @return array{success: bool, status: string, message: string, response: mixed}
     */
    protected function exceptionResult(Throwable $e): array
    {
        return [
            'success' => false,
            'status' => 'failed',
            'message' => 'Koneksi ke API Fonnte gagal: ' . $e->getMessage(),
            'response' => ['exception' => $e->getMessage()],
        ];
    }
}

