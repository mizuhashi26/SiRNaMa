<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Student;
use App\Services\ChatbotMenuService;
use App\Services\FonnteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FonnteWebhookController extends Controller
{
    public function handle(
        Request $request,
        ChatbotMenuService $chatbot,
        FonnteService $fonnteService
    ): JsonResponse {
        Log::info('FONNTE WEBHOOK RECEIVED', [
            'method' => $request->method(),
            'inputs' => $request->all(),
            'raw' => $request->getContent(),
        ]);

        if ($request->isMethod('get')) {
            return response()->json([
                'status' => true,
                'message' => 'Webhook endpoint Fonnte aktif dan siap menerima pesan.',
            ]);
        }
        $configuredSecret = (string) config('services.fonnte.webhook_secret', '');
        if ($configuredSecret !== '') {
            $providedSecret = (string) ($request->header('X-Webhook-Secret') ?? $request->input('secret', ''));
            if (! hash_equals($configuredSecret, $providedSecret)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Webhook secret tidak sesuai.',
                ], 403);
            }
        }

        $rawSender = (string) ($request->input('sender') ?? $request->input('pengirim') ?? $request->input('from') ?? '');
        $messageText = trim((string) ($request->input('message') ?? $request->input('pesan') ?? $request->input('text') ?? ''));
        $pushName = trim((string) ($request->input('name') ?? $request->input('pushname') ?? ''));

        $normalizedSender = Student::normalizePhone($rawSender);

        if ($normalizedSender === '' || $messageText === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Payload sender atau message kosong.',
            ], 422);
        }

        $student = Student::where('whatsapp_number', $normalizedSender)->first();
        $resolvedName = $student?->name
            ?? ($pushName !== '' ? $pushName : 'Mahasiswa WA +' . $normalizedSender);

        $conversation = Conversation::firstOrCreate(
            [
                'channel' => 'whatsapp',
                'external_user_id' => $normalizedSender,
            ],
            [
                'user_name' => $resolvedName,
                'contact_number' => $normalizedSender,
                'status' => Conversation::STATUS_BOT_ACTIVE,
                'last_message_at' => now(),
            ]
        );

        if ($student && $conversation->user_name !== $student->name) {
            $conversation->user_name = $student->name;
            $conversation->save();
        }

        $result = $chatbot->handleIncomingMessage($conversation, $messageText, $resolvedName);
        $botMessage = $result['bot_message'];
        $gatewayResult = null;

        if ($botMessage !== null) {
            $gatewayResult = $fonnteService->sendMessage($normalizedSender, $botMessage->message);
            $botMessage->update([
                'delivery_status' => $gatewayResult['success']
                    ? 'sent_gateway'
                    : ($gatewayResult['status'] === 'unverified_no_token' ? 'local_only' : 'failed_gateway'),
                'metadata' => [
                    'fonnte_status' => $gatewayResult['status'],
                    'fonnte_message' => $gatewayResult['message'],
                ],
            ]);
        }

        return response()->json([
            'ok' => true,
            'conversation_id' => $result['conversation']->id,
            'status' => $result['conversation']->status,
            'bot_replied' => $botMessage !== null,
            'bot_reply' => $botMessage?->message,
            'gateway_delivery' => $gatewayResult['status'] ?? 'skipped_admin_mode',
        ]);
    }
}

