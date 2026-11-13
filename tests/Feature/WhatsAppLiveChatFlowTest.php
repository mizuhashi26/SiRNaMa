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

    public function test_admin_direct_reply_from_whatsapp_app_takes_over_conversation_and_syncs_to_crm(): void
    {
        $mockFonnte = $this->mock(FonnteService::class);
        $mockFonnte->shouldReceive('isConfigured')->andReturn(true);
        $mockFonnte->shouldReceive('sendMessage')->andReturn([
            'success' => true,
            'status' => 'success',
            'message' => 'Pesan terkirim ke gateway Fonnte',
        ]);

        $admin = User::factory()->superadmin()->create(['name' => 'Admin Test']);
        $studentPhone = '628555123456';

        // 1. Mahasiswa mengirim pesan ke Bot WA
        $this->postJson(route('webhook.fonnte'), [
            'sender' => $studentPhone,
            'message' => 'Halo admin, mau tanya info KRS',
            'name' => 'Siti Aminah',
        ])->assertOk();

        $conversation = Conversation::where('channel', 'whatsapp')
            ->where('external_user_id', $studentPhone)
            ->first();

        $this->assertNotNull($conversation);
        $this->assertEquals(Conversation::STATUS_BOT_ACTIVE, $conversation->status);

        // 2. Admin membalas langsung lewat aplikasi WhatsApp di ponsel (from_me = true)
        $adminWaReplyPayload = [
            'sender' => $studentPhone,
            'message' => 'Halo Siti, pengisian KRS dibuka tanggal 15 ya.',
            'from_me' => true,
        ];

        $replyRes = $this->postJson(route('webhook.fonnte'), $adminWaReplyPayload);
        $replyRes->assertOk();
        $replyRes->assertJsonPath('status', Conversation::STATUS_ADMIN_ACTIVE);

        // Verifikasi percakapan otomatis terambil alih menjadi ADMIN_ACTIVE
        $conversation->refresh();
        $this->assertEquals(Conversation::STATUS_ADMIN_ACTIVE, $conversation->status);

        // Verifikasi pesan admin tersimpan di database CRM
        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_name' => 'Admin (WhatsApp)',
            'message' => 'Halo Siti, pengisian KRS dibuka tanggal 15 ya.',
        ]);

        // 3. Mahasiswa membalas di WA, bot tidak ikut campur karena status sudah ADMIN_ACTIVE
        $followUpRes = $this->postJson(route('webhook.fonnte'), [
            'sender' => $studentPhone,
            'message' => 'Baik min terima kasih infonya',
            'name' => 'Siti Aminah',
        ]);
        $followUpRes->assertOk();
        $this->assertNull($followUpRes->json('bot_reply'));

        // Pesan lanjutan mahasiswa tetap masuk ke CRM
        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'message' => 'Baik min terima kasih infonya',
        ]);

        // 4. Mahasiswa/Admin mengetik "#bot" untuk mengembalikan sesi ke Chatbot
        $returnBotRes = $this->postJson(route('webhook.fonnte'), [
            'sender' => $studentPhone,
            'message' => '#bot',
            'name' => 'Siti Aminah',
        ]);
        $returnBotRes->assertOk();
        $returnBotRes->assertJsonPath('status', Conversation::STATUS_BOT_ACTIVE);

        $conversation->refresh();
        $this->assertEquals(Conversation::STATUS_BOT_ACTIVE, $conversation->status);

        // 5. Verifikasi admin dapat mengembalikan ke Bot via CRM dengan pesan penutup kustom
        $conversation->update(['status' => Conversation::STATUS_ADMIN_ACTIVE]);
        $closeRes = $this->actingAs($admin)->post(route('admin.live-chat.close', $conversation), [
            'closing_note' => 'Semoga membantu, sampai jumpa kembali!',
        ]);
        $closeRes->assertRedirect();

        $conversation->refresh();
        $this->assertEquals(Conversation::STATUS_BOT_ACTIVE, $conversation->status);
        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'system',
        ]);
    }

    public function test_webhook_validates_sender_phone_format_and_non_empty_message(): void
    {
        // 1. Pesan (message) kosong harus ditolak (HTTP 422)
        $this->postJson(route('webhook.fonnte'), [
            'sender' => '081298765432',
            'message' => '   ',
        ])->assertStatus(422)
          ->assertJsonPath('ok', false)
          ->assertJsonPath('message', 'Isi pesan (message) tidak boleh kosong.');

        // 2. Sender berupa teks non-angka harus ditolak (HTTP 422)
        $this->postJson(route('webhook.fonnte'), [
            'sender' => 'admin_kampus_xyz',
            'message' => 'Halo bot',
        ])->assertStatus(422)
          ->assertJsonPath('ok', false)
          ->assertJsonPath('message', 'Format nomor pengirim (sender) tidak valid. Harus berupa angka telepon 10-15 digit.');

        // 3. Sender kurang dari 10 digit harus ditolak (HTTP 422)
        $this->postJson(route('webhook.fonnte'), [
            'sender' => '123456789',
            'message' => 'Halo bot',
        ])->assertStatus(422)
          ->assertJsonPath('ok', false);

        // 4. Sender lebih dari 15 digit harus ditolak (HTTP 422)
        $this->postJson(route('webhook.fonnte'), [
            'sender' => '1234567890123456',
            'message' => 'Halo bot',
        ])->assertStatus(422)
          ->assertJsonPath('ok', false);

        // 5. Sender valid 10-15 digit (dengan awalan 08, 62, +, atau suffix WA) harus diterima (HTTP 200)
        $validSenders = [
            '0812345678', // 10 digit
            '081234567890', // 12 digit
            '6281234567890', // 13 digit
            '+6281234567890', // 13 digit dengan +
            '081234567890@s.whatsapp.net', // dengan suffix WA
        ];

        foreach ($validSenders as $validSender) {
            $res = $this->postJson(route('webhook.fonnte'), [
                'sender' => $validSender,
                'message' => 'Halo bot',
            ]);
            $res->assertOk()
                ->assertJsonPath('ok', true);
        }
    }
}

