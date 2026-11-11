<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Student;
use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppLiveChatFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_whatsapp_to_admin_live_chat_cycle(): void
    {
        // 1. Mock FonnteService agar tidak melakukan panggilan HTTP riil keluar saat pengujian
        $mockFonnte = $this->mock(FonnteService::class);
        $mockFonnte->shouldReceive('isConfigured')->andReturn(true);
        $mockFonnte->shouldReceive('sendMessage')
            ->andReturn([
                'success' => true,
                'status' => 'success',
                'message' => 'Pesan terkirim ke gateway Fonnte',
                'data' => ['id' => 'msg-dummy-123'],
            ]);

        // Siapkan Akun Admin CRM dan Data Mahasiswa
        $admin = User::factory()->create([
            'name' => 'Admin Budi',
            'email' => 'admin.budi@unpam.ac.id',
        ]);

        $studentPhone = '6281298765432';
        Student::create([
            'nim' => '211011400888',
            'name' => 'Rian Pratama',
            'study_program' => 'Teknik Informatika S1',
            'semester' => 4,
            'whatsapp_number' => $studentPhone,
            'is_active' => true,
        ]);

        // LANGKAH 1: Mahasiswa WA pertama kali mengirim "Halo"
        $webhookPayload1 = [
            'sender' => $studentPhone,
            'message' => 'Halo min',
            'name' => 'Rian Pratama',
        ];

        $response1 = $this->postJson(route('webhook.fonnte'), $webhookPayload1);
        $response1->assertOk();
        $response1->assertJsonPath('ok', true);

        // Verifikasi percakapan terbuat dengan status BOT_ACTIVE
        $conversation = Conversation::where('channel', 'whatsapp')
            ->where('external_user_id', $studentPhone)
            ->first();

        $this->assertNotNull($conversation);
        $this->assertEquals(Conversation::STATUS_BOT_ACTIVE, $conversation->status);
        $this->assertEquals('Rian Pratama', $conversation->user_name);

        // LANGKAH 2: Mahasiswa memilih menu "3" (Hubungi Admin)
        $webhookPayload2 = [
            'sender' => $studentPhone,
            'message' => '3',
            'name' => 'Rian Pratama',
        ];

        $response2 = $this->postJson(route('webhook.fonnte'), $webhookPayload2);
        $response2->assertOk();

        // Verifikasi status berpindah ke WAITING_ADMIN
        $conversation->refresh();
        $this->assertEquals(Conversation::STATUS_WAITING_ADMIN, $conversation->status);

        // LANGKAH 3: Admin membuka Live Chat & Menekan "Ambil Alih"
        $takeoverResponse = $this->actingAs($admin)
            ->post(route('admin.live-chat.take-over', $conversation));

        $takeoverResponse->assertRedirect();

        // Verifikasi status beralih ke ADMIN_ACTIVE
        $conversation->refresh();
        $this->assertEquals(Conversation::STATUS_ADMIN_ACTIVE, $conversation->status);
        $this->assertEquals($admin->id, $conversation->assigned_admin_id);

        // LANGKAH 4: Mahasiswa WA mengirim pesan pertanyaan ke Admin
        $webhookPayload3 = [
            'sender' => $studentPhone,
            'message' => 'Permisi Pak, mau tanya syarat dispensasi pembayaran UAS bagaimana ya?',
            'name' => 'Rian Pratama',
        ];

        $response3 = $this->postJson(route('webhook.fonnte'), $webhookPayload3);
        $response3->assertOk();

        // Pastikan bot TIDAK membalas karena sedang ditangani Admin aktif
        $this->assertNull($response3->json('bot_reply'));

        // Pastikan pesan mahasiswa tersimpan di pesan percakapan
        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'message' => 'Permisi Pak, mau tanya syarat dispensasi pembayaran UAS bagaimana ya?',
        ]);

        // LANGKAH 5: Admin membalas pesan mahasiswa dari Dashboard Live Chat
        $replyResponse = $this->actingAs($admin)
            ->post(route('admin.live-chat.reply', $conversation), [
                'message' => 'Halo Rian, untuk dispensasi silakan ajukan surat ke Bagian Keuangan melalui loket B.',
            ]);

        $replyResponse->assertRedirect();
        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_name' => 'Admin Budi',
            'message' => "Halo Rian, untuk dispensasi silakan ajukan surat ke Bagian Keuangan melalui loket B.\n\n-Admin Budi",
        ]);

        // LANGKAH 6: Admin mengakhiri sesi dan mengembalikan ke Bot
        $closeResponse = $this->actingAs($admin)
            ->post(route('admin.live-chat.close', $conversation));

        $closeResponse->assertRedirect();

        // Verifikasi status kembali ke BOT_ACTIVE
        $conversation->refresh();
        $this->assertEquals(Conversation::STATUS_BOT_ACTIVE, $conversation->status);

        // LANGKAH 7: Mahasiswa WA chat lagi, Bot kembali melayani
        $webhookPayload4 = [
            'sender' => $studentPhone,
            'message' => 'Terima kasih',
            'name' => 'Rian Pratama',
        ];

        $response4 = $this->postJson(route('webhook.fonnte'), $webhookPayload4);
        $response4->assertOk();
        $this->assertNotNull($response4->json('bot_reply'));
    }

    public function test_admin_can_use_whatsapp_simulation_endpoint(): void
    {
        $admin = User::factory()->superadmin()->create();

        $response = $this->actingAs($admin)->post(route('admin.live-chat.simulate-whatsapp'), [
            'sender' => '628999111222',
            'message' => '3',
            'name' => 'Mahasiswa Uji Coba',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('conversations', [
            'channel' => 'whatsapp',
            'external_user_id' => '628999111222',
            'status' => Conversation::STATUS_WAITING_ADMIN,
        ]);
    }

    public function test_unregistered_whatsapp_sender_appears_on_dashboard_and_live_chat(): void
    {
        $mockFonnte = $this->mock(FonnteService::class);
        $mockFonnte->shouldReceive('isConfigured')->andReturn(true);
        $mockFonnte->shouldReceive('sendMessage')->andReturn([
            'success' => true,
            'status' => 'success',
            'message' => 'Pesan terkirim ke gateway Fonnte',
            'data' => ['id' => 'msg-dummy-999'],
        ]);

        $admin = User::factory()->superadmin()->create();

        // 1. Nomor tidak terdaftar di database mengirim pesan WhatsApp ke Bot
        $unregisteredPhone = '6283143240894';
        $webhookPayload = [
            'sender' => $unregisteredPhone,
            'message' => 'Halo min mau tanya info kampus',
            'name' => 'Calon Mahasiswa',
        ];

        $webhookRes = $this->postJson(route('webhook.fonnte'), $webhookPayload);
        $webhookRes->assertOk();

        // Verifikasi percakapan tersimpan dengan status BOT_ACTIVE
        $conversation = Conversation::where('channel', 'whatsapp')
            ->where('external_user_id', $unregisteredPhone)
            ->first();

        $this->assertNotNull($conversation);
        $this->assertEquals(Conversation::STATUS_BOT_ACTIVE, $conversation->status);

        // 2. Dashboard admin harus menampilkan percakapan ini
        $dashboardRes = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardRes->assertOk();
        $dashboardRes->assertSee('Calon Mahasiswa');
        $dashboardRes->assertSee($unregisteredPhone);
        $dashboardRes->assertSee('Kontak Baru');
        $dashboardRes->assertSee('Bot Aktif');

        // Tab bot aktif juga menampilkan percakapan
        $tabBotRes = $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'bot']));
        $tabBotRes->assertOk();
        $tabBotRes->assertSee('Calon Mahasiswa');

        // 3. Live chat juga menampilkan percakapan & tanda belum terdaftar
        $liveChatRes = $this->actingAs($admin)->get(route('admin.live-chat.index', ['conversation_id' => $conversation->id]));
        $liveChatRes->assertOk();
        $liveChatRes->assertSee('Calon Mahasiswa');
        $liveChatRes->assertSee('Nomor Belum Terdaftar');
        $liveChatRes->assertSee('Daftarkan Mahasiswa');

        // 4. Form tambah mahasiswa melakukan prefill nomor & nama
        $createStudentRes = $this->actingAs($admin)->get(route('admin.students.create', [
            'whatsapp_number' => $unregisteredPhone,
            'name' => 'Calon Mahasiswa',
        ]));
        $createStudentRes->assertOk();
        $createStudentRes->assertSee($unregisteredPhone);
        $createStudentRes->assertSee('Calon Mahasiswa');
    }
}

