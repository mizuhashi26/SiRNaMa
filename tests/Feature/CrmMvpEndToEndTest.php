<?php

namespace Tests\Feature;

use App\Models\CampusInformation;
use App\Models\Conversation;
use App\Models\PaymentReminder;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CrmMvpEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_portal_shows_only_published_information_and_simulation_banner(): void
    {
        CampusInformation::create([
            'title' => 'Pengumuman Akademik Terbit Simulasi',
            'slug' => 'pengumuman-akademik-terbit-simulasi',
            'category' => 'akademik',
            'summary' => 'Ringkasan pengumuman terbit',
            'content' => 'Isi lengkap pengumuman terbit.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        CampusInformation::create([
            'title' => 'Draft Rahasia Belum Terbit',
            'slug' => 'draft-rahasia-belum-terbit',
            'category' => 'akademik',
            'summary' => 'Ringkasan draft',
            'content' => 'Isi draft.',
            'is_published' => false,
        ]);

        $this->get('/')->assertRedirect('/admin');

        $response = $this->get('/portal');

        $response->assertOk();
        $response->assertSee('Simulasi Akademik');
        $response->assertSee('Pengumuman Akademik Terbit Simulasi');
        $response->assertDontSee('Draft Rahasia Belum Terbit');
    }

    public function test_admin_authentication_and_student_and_information_crud(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@unpam-simulasi.ac.id',
            'password' => bcrypt('password123'),
        ]);

        // Guest tidak boleh mengakses dashboard admin
        $this->get('/admin')->assertRedirect('/login');

        // Login admin
        $loginResponse = $this->post('/login', [
            'email' => 'admin@unpam-simulasi.ac.id',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);

        // Tambah mahasiswa dummy dengan nomor 0812... -> dinormalisasi ke 62812...
        $this->post('/admin/students', [
            'nim' => '2110114099',
            'name' => 'Andi Wijaya (Simulasi)',
            'study_program' => 'Teknik Informatika S1',
            'semester' => 5,
            'whatsapp_number' => '0812-9988-7766',
            'is_active' => '1',
            'notes' => 'Dummy test',
        ])->assertRedirect('/admin/students');

        $this->assertDatabaseHas('students', [
            'nim' => '2110114099',
            'whatsapp_number' => '6281299887766',
            'is_active' => true,
        ]);

        // Tambah informasi kampus baru -> langsung tampil di website publik
        $this->post('/admin/informations', [
            'title' => 'Jadwal UAS Semester Ganjil Simulasi',
            'category' => 'jadwal_ujian',
            'summary' => 'Info pelaksanaan UAS',
            'content' => 'Ujian dilaksanakan mulai tanggal 15 Desember.',
            'is_published' => '1',
            'is_pinned' => '1',
        ])->assertRedirect('/admin/informations');

        $this->get('/portal')->assertSee('Jadwal UAS Semester Ganjil Simulasi');
    }

    public function test_web_chatbot_shares_single_source_of_truth_and_transitions_to_waiting_admin(): void
    {
        CampusInformation::create([
            'title' => 'Info Pembayaran UKT Semester Ini (Simulasi)',
            'slug' => 'info-pembayaran-ukt-semester-ini',
            'category' => 'pembayaran',
            'content' => 'Batas akhir pembayaran umum tanggal 10 setiap bulan.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Buka sesi awal chatbot website
        $sessionRes = $this->getJson('/chat/session');
        $sessionRes->assertOk()
            ->assertJsonPath('status', Conversation::STATUS_BOT_ACTIVE);

        // Pilih Menu 2 (Informasi Pembayaran) -> mengambil data dari campus_informations
        $menu2Res = $this->postJson('/chat/message', [
            'message' => '2',
            'visitor_name' => 'Budi Mahasiswa Web',
        ]);
        $menu2Res->assertOk()
            ->assertJsonPath('status', Conversation::STATUS_BOT_ACTIVE);
        $this->assertStringContainsString(
            'Info Pembayaran UKT Semester Ini (Simulasi)',
            collect($menu2Res->json('messages'))->last()['message']
        );

        // Pilih Menu 3 (Hubungi Admin) -> status berubah menjadi WAITING_ADMIN
        $menu3Res = $this->postJson('/chat/message', [
            'message' => '3',
        ]);
        $menu3Res->assertOk()
            ->assertJsonPath('status', Conversation::STATUS_WAITING_ADMIN);

        // Kirim pertanyaan bebas saat WAITING_ADMIN -> pesan disimpan tanpa ditimpa balasan menu bot
        $questionRes = $this->postJson('/chat/message', [
            'message' => 'Halo Pak Admin, saya ingin bertanya tentang dispensasi jadwal ujian.',
        ]);
        $questionRes->assertOk()
            ->assertJsonPath('status', Conversation::STATUS_WAITING_ADMIN);
        $this->assertSame(
            'Halo Pak Admin, saya ingin bertanya tentang dispensasi jadwal ujian.',
            collect($questionRes->json('messages'))->last()['message']
        );
    }

    public function test_whatsapp_webhook_and_admin_live_chat_takeover_reply_and_close_flow(): void
    {
        config(['services.fonnte.token' => 'test-fonnte-token-123']);

        Http::fake([
            'https://api.fonnte.com/*' => Http::response(['status' => true, 'detail' => 'sent'], 200),
        ]);

        Student::create([
            'nim' => '2110114001',
            'name' => 'Rizky Pratama (Simulasi)',
            'study_program' => 'Teknik Informatika S1',
            'semester' => 5,
            'whatsapp_number' => '6281200000001',
            'is_active' => true,
        ]);

        // 1. Mahasiswa mengirim pesan "3" (Hubungi Admin) melalui WhatsApp (Webhook Fonnte)
        $webhookRes = $this->postJson('/api/webhook/fonnte', [
            'sender' => '081200000001',
            'message' => '3',
            'name' => 'Rizky WA',
        ]);

        $webhookRes->assertOk()
            ->assertJsonPath('status', Conversation::STATUS_WAITING_ADMIN)
            ->assertJsonPath('bot_replied', true);

        $conversation = Conversation::where('channel', 'whatsapp')
            ->where('external_user_id', '6281200000001')
            ->firstOrFail();

        $this->assertSame('Rizky Pratama (Simulasi)', $conversation->user_name);

        // 2. Admin login dan menekan Ambil Alih
        $admin = User::factory()->create(['name' => 'Admin Akademik']);
        $this->actingAs($admin)
            ->post("/admin/live-chat/{$conversation->id}/take-over")
            ->assertRedirect();

        $conversation->refresh();
        $this->assertSame(Conversation::STATUS_ADMIN_ACTIVE, $conversation->status);

        // 3. Saat ADMIN_ACTIVE, mahasiswa membalas via WA -> bot tidak boleh menimpa
        $msgWhileAdminActive = $this->postJson('/api/webhook/fonnte', [
            'sender' => '6281200000001',
            'message' => 'Mohon info ruang ujian susulan Pak.',
        ]);
        $msgWhileAdminActive->assertOk()
            ->assertJsonPath('status', Conversation::STATUS_ADMIN_ACTIVE)
            ->assertJsonPath('bot_replied', false);

        // 4. Admin mengirim balasan dari dashboard -> dikirim via Fonnte API
        $this->actingAs($admin)
            ->post("/admin/live-chat/{$conversation->id}/reply", [
                'message' => 'Baik Rizky, ruang ujian susulan berada di Gedung Viktor Lt. 4 Ruang 402.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'delivery_status' => 'sent_gateway',
        ]);

        // 5. Admin mengakhiri percakapan -> status menjadi CLOSED
        $this->actingAs($admin)
            ->post("/admin/live-chat/{$conversation->id}/close")
            ->assertRedirect();

        $conversation->refresh();
        $this->assertSame(Conversation::STATUS_CLOSED, $conversation->status);

        // 6. Mahasiswa mengirim pesan lagi setelah CLOSED -> bot kembali aktif melayani menu
        $reopenRes = $this->postJson('/api/webhook/fonnte', [
            'sender' => '6281200000001',
            'message' => '0',
        ]);
        $reopenRes->assertOk()
            ->assertJsonPath('status', Conversation::STATUS_BOT_ACTIVE)
            ->assertJsonPath('bot_replied', true);
    }

    public function test_payment_reminder_manual_dispatch_and_scheduled_command(): void
    {
        Student::create([
            'nim' => '2110114001',
            'name' => 'Mahasiswa Aktif 1',
            'study_program' => 'Teknik Informatika S1',
            'semester' => 3,
            'whatsapp_number' => '6281200000001',
            'is_active' => true,
        ]);

        Student::create([
            'nim' => '2110114002',
            'name' => 'Mahasiswa Nonaktif',
            'study_program' => 'Teknik Informatika S1',
            'semester' => 3,
            'whatsapp_number' => '6281200000002',
            'is_active' => false,
        ]);

        $reminder = PaymentReminder::create([
            'title' => 'Reminder Pembayaran Umum Semester Ganjil',
            'message' => 'Batas pembayaran kuliah umum adalah tanggal 10 bulan ini.',
            'scheduled_at' => now()->subMinutes(5),
            'status' => 'scheduled',
        ]);

        // Kondisi 1: Token Fonnte kosong -> dicatat jujur sebagai unverified_no_token
        config(['services.fonnte.token' => '']);
        $this->artisan('reminders:send-due')->assertExitCode(0);

        $reminder->refresh();
        $this->assertSame('unverified_no_token', $reminder->status);
        $this->assertSame(1, $reminder->total_targets);
        $this->assertSame(0, $reminder->sent_count);
        $this->assertSame(1, $reminder->failed_count);

        // Kondisi 2: Token Fonnte diisi dan dikirim manual oleh Admin -> status menjadi sent
        config(['services.fonnte.token' => 'valid-fonnte-token']);
        Http::fake([
            'https://api.fonnte.com/*' => Http::response(['status' => true], 200),
        ]);

        $admin = User::factory()->create();
        $this->actingAs($admin)
            ->post("/admin/reminders/{$reminder->id}/send-now")
            ->assertRedirect("/admin/reminders/{$reminder->id}");

        $reminder->refresh();
        $this->assertSame('sent', $reminder->status);
        $this->assertSame(1, $reminder->sent_count);
        $this->assertSame(0, $reminder->failed_count);
    }
}

