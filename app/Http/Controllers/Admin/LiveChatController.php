<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\FonnteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LiveChatController extends Controller
{
    public function index(Request $request, FonnteService $fonnteService): View
    {
        $statusFilter = (string) $request->query('status', '');
        $channelFilter = (string) $request->query('channel', '');

        $conversations = Conversation::with('assignedAdmin')
            ->when(
                $statusFilter !== '' && array_key_exists($statusFilter, Conversation::STATUS_LABELS),
                fn ($q) => $q->where('status', $statusFilter)
            )
            ->when(
                in_array($channelFilter, ['web', 'whatsapp'], true),
                fn ($q) => $q->where('channel', $channelFilter)
            )
            ->orderByRaw("CASE
                WHEN status = 'WAITING_ADMIN' THEN 0
                WHEN status = 'ADMIN_ACTIVE' THEN 1
                WHEN status = 'BOT_ACTIVE' THEN 2
                ELSE 3
            END")
            ->orderByDesc('last_message_at')
            ->get();

        $activeConversation = null;
        $selectedId = (int) $request->query('conversation_id', 0);

        if ($selectedId > 0) {
            $activeConversation = Conversation::with(['messages', 'assignedAdmin'])->find($selectedId);
        } elseif ($conversations->isNotEmpty()) {
            $activeConversation = Conversation::with(['messages', 'assignedAdmin'])->find($conversations->first()->id);
        }

        return view('admin.live-chat.index', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'statusFilter' => $statusFilter,
            'channelFilter' => $channelFilter,
            'statusLabels' => Conversation::STATUS_LABELS,
            'fonnteConfigured' => $fonnteService->isConfigured(),
        ]);
    }

    public function takeOver(Conversation $conversation, FonnteService $fonnteService): RedirectResponse
    {
        $admin = Auth::user();

        $conversation->update([
            'status' => Conversation::STATUS_ADMIN_ACTIVE,
            'assigned_admin_id' => $admin?->id,
            'taken_over_at' => now(),
            'closed_at' => null,
            'last_message_at' => now(),
        ]);

        $noticeText = "Percakapan telah diambil alih oleh {$admin?->name} (Status: Admin Aktif). Silakan sampaikan keperluan Anda.";

        $systemMsg = $conversation->messages()->create([
            'sender_type' => 'system',
            'sender_name' => $admin?->name ?? 'Admin CRM',
            'message' => $noticeText,
            'delivery_status' => 'delivered',
        ]);

        if ($conversation->channel === 'whatsapp' && filled($conversation->contact_number)) {
            $gatewayResult = $fonnteService->sendMessage($conversation->contact_number, $noticeText);
            $systemMsg->update([
                'delivery_status' => $gatewayResult['success']
                    ? 'sent_gateway'
                    : ($gatewayResult['status'] === 'unverified_no_token' ? 'local_only' : 'failed_gateway'),
            ]);
        }

        return redirect()->route('admin.live-chat.index', ['conversation_id' => $conversation->id])
            ->with('success', 'Percakapan berhasil diambil alih (Status: Admin Aktif).');
    }

    public function reply(Request $request, Conversation $conversation, FonnteService $fonnteService): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'message.required' => 'Isi pesan balasan tidak boleh kosong.',
        ]);

        $admin = Auth::user();

        // Pastikan status menjadi ADMIN_ACTIVE saat admin mengirim balasan
        if ($conversation->status !== Conversation::STATUS_ADMIN_ACTIVE) {
            $conversation->status = Conversation::STATUS_ADMIN_ACTIVE;
            $conversation->assigned_admin_id = $admin?->id;
            $conversation->taken_over_at = $conversation->taken_over_at ?? now();
        }

        $conversation->last_message_at = now();
        $conversation->save();

        $deliveryStatus = 'delivered';
        $metadata = null;
        $flashType = 'success';
        $flashNotice = 'Balasan berhasil dikirim ke mahasiswa.';

        if ($conversation->channel === 'whatsapp' && filled($conversation->contact_number)) {
            $waMessage = "*[Admin Layanan UNPAM - {$admin?->name}]*\n" . $validated['message'];
            $gatewayResult = $fonnteService->sendMessage($conversation->contact_number, $waMessage);

            if ($gatewayResult['success']) {
                $deliveryStatus = 'sent_gateway';
                $flashNotice = 'Balasan berhasil dikirim ke WhatsApp mahasiswa melalui Fonnte.';
            } elseif ($gatewayResult['status'] === 'unverified_no_token') {
                $deliveryStatus = 'local_only';
                $flashType = 'warning';
                $flashNotice = 'Balasan disimpan di dashboard, namun belum terkirim ke WhatsApp karena FONNTE_TOKEN pada .env belum diisi.';
            } else {
                $deliveryStatus = 'failed_gateway';
                $flashType = 'warning';
                $flashNotice = 'Balasan disimpan di dashboard, tetapi pengiriman ke API Fonnte gagal: ' . $gatewayResult['message'];
            }

            $metadata = [
                'fonnte_status' => $gatewayResult['status'],
                'fonnte_message' => $gatewayResult['message'],
            ];
        }

        $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_name' => $admin?->name ?? 'Admin CRM',
            'message' => $validated['message'],
            'delivery_status' => $deliveryStatus,
            'metadata' => $metadata,
        ]);

        return redirect()->route('admin.live-chat.index', ['conversation_id' => $conversation->id])
            ->with($flashType, $flashNotice);
    }

    public function close(Conversation $conversation, FonnteService $fonnteService): RedirectResponse
    {
        $admin = Auth::user();

        // Saat admin leave, alihkan status kembali ke BOT_ACTIVE (bukan selesai/closed)
        $conversation->update([
            'status' => Conversation::STATUS_BOT_ACTIVE,
            'assigned_admin_id' => null,
            'closed_at' => now(),
            'last_message_at' => now(),
        ]);

        $closingText = 'Admin telah menyelesaikan pendampingan dan meninggalkan percakapan. Sesi otomatis dialihkan kembali ke Chatbot Layanan Mahasiswa (Status: Bot Aktif). Silakan pilih menu atau ketik pesan Anda.';

        $systemMsg = $conversation->messages()->create([
            'sender_type' => 'system',
            'sender_name' => $admin?->name ?? 'Admin CRM',
            'message' => $closingText,
            'delivery_status' => 'delivered',
        ]);

        if ($conversation->channel === 'whatsapp' && filled($conversation->contact_number)) {
            $gatewayResult = $fonnteService->sendMessage($conversation->contact_number, $closingText);
            $systemMsg->update([
                'delivery_status' => $gatewayResult['success']
                    ? 'sent_gateway'
                    : ($gatewayResult['status'] === 'unverified_no_token' ? 'local_only' : 'failed_gateway'),
            ]);
        }

        return redirect()->route('admin.live-chat.index', ['conversation_id' => $conversation->id])
            ->with('success', 'Admin telah meninggalkan percakapan. Sesi berhasil dialihkan kembali ke Chatbot aktif (Status: Bot Aktif).');
    }

    public function poll(Conversation $conversation): JsonResponse
    {
        $conversation->load('messages');

        return response()->json([
            'id' => $conversation->id,
            'status' => $conversation->status,
            'status_label' => $conversation->status_label,
            'messages' => $conversation->messages->map(fn ($msg) => [
                'id' => $msg->id,
                'sender_type' => $msg->sender_type,
                'sender_name' => $msg->sender_name,
                'message' => $msg->message,
                'delivery_status' => $msg->delivery_status,
                'time' => $msg->created_at?->format('H:i'),
            ])->values(),
        ]);
    }
}

