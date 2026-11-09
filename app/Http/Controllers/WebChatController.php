<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\ChatbotMenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebChatController extends Controller
{
    public function session(Request $request, ChatbotMenuService $chatbot): JsonResponse
    {
        $conversation = $this->resolveWebConversation($request);

        // Jika percakapan baru dan belum punya pesan, tampilkan sambutan awal bot
        if ($conversation->messages()->count() === 0) {
            $conversation->messages()->create([
                'sender_type' => 'bot',
                'sender_name' => 'Chatbot Layanan UNPAM',
                'message' => $chatbot->buildMainMenuText(),
                'delivery_status' => 'delivered',
            ]);
            $conversation->last_message_at = now();
            $conversation->save();
        }

        return response()->json($this->formatConversationPayload($conversation, $chatbot));
    }

    public function sendMessage(Request $request, ChatbotMenuService $chatbot): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'visitor_name' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:30'],
        ]);

        $conversation = $this->resolveWebConversation($request);

        if (! empty($validated['visitor_name'])) {
            $conversation->user_name = trim($validated['visitor_name']);
        }

        if (! empty($validated['contact_number'])) {
            $conversation->contact_number = trim($validated['contact_number']);
        }

        $conversation->save();

        $result = $chatbot->handleIncomingMessage(
            $conversation,
            $validated['message'],
            $conversation->user_name
        );

        return response()->json(
            $this->formatConversationPayload($result['conversation'], $chatbot, $result['options'])
        );
    }

    protected function resolveWebConversation(Request $request): Conversation
    {
        $token = (string) $request->session()->get('web_chat_token', '');

        if ($token === '') {
            $token = 'web_' . Str::lower(Str::random(16));
            $request->session()->put('web_chat_token', $token);
        }

        $shortCode = strtoupper(substr(str_replace('web_', '', $token), 0, 5));

        return Conversation::firstOrCreate(
            [
                'channel' => 'web',
                'external_user_id' => $token,
            ],
            [
                'user_name' => "Mahasiswa Web #{$shortCode}",
                'status' => Conversation::STATUS_BOT_ACTIVE,
                'last_message_at' => now(),
            ]
        );
    }

    /**
     * @param array<int, array{code: string, label: string}>|null $customOptions
     * @return array<string, mixed>
     */
    protected function formatConversationPayload(
        Conversation $conversation,
        ChatbotMenuService $chatbot,
        ?array $customOptions = null
    ): array {
        $conversation->load('messages');

        $options = $customOptions ?? $chatbot->resolveCurrentOptions($conversation);

        return [
            'conversation_id' => $conversation->id,
            'user_name' => $conversation->user_name,
            'contact_number' => $conversation->contact_number,
            'status' => $conversation->status,
            'status_label' => $conversation->status_label,
            'options' => $options,
            'messages' => $conversation->messages->map(fn ($msg) => [
                'id' => $msg->id,
                'sender_type' => $msg->sender_type,
                'sender_name' => $msg->sender_name,
                'message' => $msg->message,
                'time' => $msg->created_at?->format('H:i'),
            ])->values(),
        ];
    }
}

