<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Conversation;
use App\Models\Student;
use App\Services\ChatbotMenuService;
use App\Services\FonnteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
            if (!hash_equals($configuredSecret, $providedSecret)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Webhook secret tidak sesuai.',
                ], 403);
            }
        }

        // Normalisasi key payload alternatif dari berbagai format webhook Fonnte
        $rawSenderInput = $request->input('sender') ?? $request->input('pengirim') ?? $request->input('from');
        $rawMessageInput = $request->input('message') ?? $request->input('pesan') ?? $request->input('text');
        $rawTargetInput = $request->input('target') ?? $request->input('receiver') ?? $request->input('to');
        $rawNameInput = $request->input('name') ?? $request->input('pushname');

        // Validasi menggunakan Validator Laravel untuk menolak tipe ngawur (array/null/object) dan payload ekstrem
        $validator = Validator::make([
            'sender' => $rawSenderInput,
            'message' => $rawMessageInput,
            'target' => $rawTargetInput,
            'name' => $rawNameInput,
        ], [
            'sender' => ['required', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:5000'],
            'target' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:150'],
        ], [
            'sender.required' => 'Field pengirim (sender) wajib ada.',
            'sender.string' => 'Field pengirim (sender) harus berupa string.',
            'sender.max' => 'Panjang pengirim (sender) maksimal 50 karakter.',
            'message.required' => 'Isi pesan (message) tidak boleh kosong.',
            'message.string' => 'Isi pesan (message) harus berupa string.',
            'message.max' => 'Panjang pesan (message) maksimal 5000 karakter.',
            'target.string' => 'Field target harus berupa string.',
            'target.max' => 'Panjang target maksimal 50 karakter.',
            'name.string' => 'Field nama harus berupa string.',
            'name.max' => 'Panjang nama maksimal 150 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $rawSender = (string) $rawSenderInput;
        $rawTarget = (string) ($rawTargetInput ?? '');
        $messageText = trim((string) $rawMessageInput);
        $pushName = trim((string) ($rawNameInput ?? ''));

        // 1. Validasi isi pesan (message): tidak boleh string kosong setelah di-trim
        if ($messageText === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Isi pesan (message) tidak boleh kosong.',
            ], 422);
        }

        // 2. Validasi format nomor pengirim (sender): wajib hanya angka 0-9, panjang 10–15 digit
        $cleanSender = preg_replace('/(@s\.whatsapp\.net|@c\.us)$/i', '', trim($rawSender));
        $cleanSender = ltrim($cleanSender, '+');
        $cleanSender = str_replace([' ', '-'], '', $cleanSender);

        if (!preg_match('/^[0-9]{10,15}$/', $cleanSender)) {
            return response()->json([
                'ok' => false,
                'message' => 'Format nomor pengirim (sender) tidak valid. Harus berupa angka telepon 10-15 digit.',
            ], 422);
        }

        // Cek apakah pesan dikirim oleh nomor/device WA admin sendiri (outgoing/reply)
        $isFromMe = filter_var(
            $request->input('from_me') 
            ?? $request->input('is_from_me') 
            ?? $request->input('fromMe') 
            ?? ($request->input('status') === 'sent')
            ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        // Jika from_me aktif dan rawTarget terisi, validasi juga format nomor target
        if ($isFromMe && $rawTarget !== '') {
            $cleanTarget = preg_replace('/(@s\.whatsapp\.net|@c\.us)$/i', '', trim($rawTarget));
            $cleanTarget = ltrim($cleanTarget, '+');
            $cleanTarget = str_replace([' ', '-'], '', $cleanTarget);

            if (!preg_match('/^[0-9]{10,15}$/', $cleanTarget)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Format nomor tujuan (target) tidak valid. Harus berupa angka telepon 10-15 digit.',
                ], 422);
            }
        }

        $normalizedSender = Student::normalizePhone($cleanSender);
        $normalizedTarget = ($isFromMe && $rawTarget !== '') ? Student::normalizePhone($rawTarget) : '';

        // Jika from_me aktif dan target nomor mahasiswa terisi, lawan bicaranya adalah target.
        // Jika target kosong, gunakan normalizedSender.
        $studentPhone = ($isFromMe && $normalizedTarget !== '') ? $normalizedTarget : $normalizedSender;

        $student = Student::where('whatsapp_number', $studentPhone)->first();
        $resolvedName = $student?->name
            ?? ($pushName !== '' ? $pushName : 'Mahasiswa WA +' . $studentPhone);

        // Temukan atau buat sesi percakapan WhatsApp mahasiswa
        $conversation = Conversation::firstOrCreate(
            [
                'channel' => 'whatsapp',
                'external_user_id' => $studentPhone,
            ],
            [
                'user_name' => $resolvedName,
                'contact_number' => $studentPhone,
                'status' => $isFromMe ? Conversation::STATUS_ADMIN_ACTIVE : Conversation::STATUS_BOT_ACTIVE,
                'last_message_at' => now(),
            ]
        );

        if ($student && $conversation->user_name !== $student->name) {
            $conversation->user_name = $student->name;
            $conversation->save();
        }

        $lowerMsg = mb_strtolower(trim($messageText));
        $strippedMsg = ltrim($lowerMsg, '#!/');
        $isReturnToBotCommand = in_array($strippedMsg, [
            '0', 'bot', 'kembali ke bot', 'kembalikan ke bot',
            'keluar', 'menu', 'menu utama', 'utama', 'batal',
            'kembali', 'selesai', 'akhiri', 'exit', 'back'
        ], true) || in_array($lowerMsg, ['#bot', '!bot', '/bot', '#selesai', '!selesai'], true);

        // KASUS 0: Perintah Kembali ke Bot (baik dari Admin maupun Mahasiswa di WhatsApp)
        if ($isReturnToBotCommand) {
            $conversation->status = Conversation::STATUS_BOT_ACTIVE;
            $conversation->assigned_admin_id = null;
            $conversation->closed_at = now();
            $conversation->last_message_at = now();
            $conversation->save();

            $institution = AppSetting::getValue('app_institution_name', 'Universitas Pamulang');
            $logoText = AppSetting::getValue('app_logo_text', 'UNPAM');
            $replyText = "Sesi bantuan admin telah selesai. Layanan otomatis dialihkan kembali ke Chatbot aktif SiRNaMa ({$institution}).\n\n" . $chatbot->buildMainMenuText();
            $botMsg = $conversation->messages()->create([
                'sender_type' => 'bot',
                'sender_name' => "Chatbot SiRNaMa {$logoText}",
                'message' => $replyText,
                'delivery_status' => 'delivered',
            ]);

            $fonnteService->sendMessage($studentPhone, $replyText);

            return response()->json([
                'ok' => true,
                'message' => 'Sesi berhasil dialihkan kembali ke Chatbot via perintah WhatsApp.',
                'conversation_id' => $conversation->id,
                'status' => Conversation::STATUS_BOT_ACTIVE,
                'bot_replied' => true,
                'bot_reply' => $replyText,
            ]);
        }

        // KASUS 1: Pesan dari Admin Langsung melalui Aplikasi WhatsApp
        if ($isFromMe) {
            // Otomatis ubah status menjadi ADMIN_ACTIVE (ambil alih bot)
            $conversation->status = Conversation::STATUS_ADMIN_ACTIVE;
            $conversation->last_message_at = now();
            $conversation->save();

            // Rekam balasan pesan admin ke dalam riwayat CRM
            $adminMessage = $conversation->messages()->create([
                'sender_type' => 'admin',
                'sender_name' => 'Admin (WhatsApp)',
                'message' => $messageText,
                'delivery_status' => 'delivered',
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Pesan balasan admin dari WhatsApp berhasil disinkronkan ke CRM.',
                'conversation_id' => $conversation->id,
                'message_id' => $adminMessage->id,
                'status' => Conversation::STATUS_ADMIN_ACTIVE,
            ]);
        }

        // KASUS 2: Pesan Masuk dari Mahasiswa / Pengguna WhatsApp
        $result = $chatbot->handleIncomingMessage($conversation, $messageText, $resolvedName);
        $botMessage = $result['bot_message'];
        $gatewayResult = null;

        if ($botMessage !== null) {
            $gatewayResult = $fonnteService->sendMessage($studentPhone, $botMessage->message);
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

