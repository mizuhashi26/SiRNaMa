<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_admin_can_access_allowed_modules(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Reguler',
            'role' => User::ROLE_ADMIN,
        ]);

        // 1. Dashboard Ringkasan
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        // 2. Informasi Kampus
        $this->actingAs($admin)->get(route('admin.informations.index'))->assertOk();

        // 3. Pengingat Mahasiswa
        $this->actingAs($admin)->get(route('admin.reminders.index'))->assertOk();

        // 4. Live Chat
        $this->actingAs($admin)->get(route('admin.live-chat.index'))->assertOk();
    }

    public function test_regular_admin_is_forbidden_from_superadmin_modules(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        // Mahasiswa (Student)
        $this->actingAs($admin)->get(route('admin.students.index'))->assertForbidden();

        // Pengaturan Sistem (Settings)
        $this->actingAs($admin)->get(route('admin.settings.index'))->assertForbidden();

        // Kelola Admin (Users)
        $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users.create'))->assertForbidden();
    }

    public function test_superadmin_can_manage_admin_users_crud(): void
    {
        $superadmin = User::factory()->superadmin()->create([
            'name' => 'Super Admin Utama',
            'email' => 'super@unpam.ac.id',
        ]);

        // 1. Akses index kelola admin
        $indexRes = $this->actingAs($superadmin)->get(route('admin.users.index'));
        $indexRes->assertOk();
        $indexRes->assertSee('Kelola Akun Admin');

        // 2. Buat admin baru
        $storeRes = $this->actingAs($superadmin)->post(route('admin.users.store'), [
            'name' => 'Admin Staff 1',
            'email' => 'staff1@unpam.ac.id',
            'role' => User::ROLE_ADMIN,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $storeRes->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Admin Staff 1',
            'email' => 'staff1@unpam.ac.id',
            'role' => User::ROLE_ADMIN,
        ]);

        $createdAdmin = User::where('email', 'staff1@unpam.ac.id')->firstOrFail();

        // 3. Edit admin
        $editRes = $this->actingAs($superadmin)->get(route('admin.users.edit', $createdAdmin));
        $editRes->assertOk();
        $editRes->assertSee('staff1@unpam.ac.id');

        // 4. Update admin
        $updateRes = $this->actingAs($superadmin)->put(route('admin.users.update', $createdAdmin), [
            'name' => 'Admin Staff 1 Updated',
            'email' => 'staff1.updated@unpam.ac.id',
            'role' => User::ROLE_ADMIN,
        ]);

        $updateRes->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $createdAdmin->id,
            'name' => 'Admin Staff 1 Updated',
            'email' => 'staff1.updated@unpam.ac.id',
        ]);

        // 5. Hapus admin
        $deleteRes = $this->actingAs($superadmin)->delete(route('admin.users.destroy', $createdAdmin));
        $deleteRes->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $createdAdmin->id]);
    }

    public function test_superadmin_cannot_delete_self(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $res = $this->actingAs($superadmin)->delete(route('admin.users.destroy', $superadmin));
        $res->assertRedirect(route('admin.users.index'));
        $res->assertSessionHas('warning');

        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }

    public function test_superadmin_cannot_delete_another_superadmin(): void
    {
        $superadmin1 = User::factory()->superadmin()->create();
        $superadmin2 = User::factory()->superadmin()->create();

        $res = $this->actingAs($superadmin1)->delete(route('admin.users.destroy', $superadmin2));
        $res->assertRedirect(route('admin.users.index'));
        $res->assertSessionHas('warning');

        $this->assertDatabaseHas('users', ['id' => $superadmin2->id]);
    }

    public function test_live_chat_reply_appends_admin_name_suffix(): void
    {
        $admin = User::factory()->create([
            'name' => 'admin a',
            'role' => User::ROLE_ADMIN,
        ]);

        $conversation = Conversation::create([
            'channel' => 'website',
            'external_user_id' => 'web-sess-12345',
            'session_id' => 'web-sess-12345',
            'user_name' => 'Mahasiswa Test',
            'status' => Conversation::STATUS_WAITING_ADMIN,
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.live-chat.reply', $conversation), [
            'message' => 'Silakan datang ke loket Biro Administrasi.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('conversation_messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_name' => 'admin a',
            'message' => "Silakan datang ke loket Biro Administrasi.\n\n-admin a",
        ]);
    }

    public function test_admin_chat_can_only_access_live_chat_and_is_forbidden_from_other_modules(): void
    {
        $adminChat = User::factory()->create([
            'name' => 'CS Chat Only',
            'email' => 'cs.chat@unpam.ac.id',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN_CHAT,
        ]);

        // 1. Login mengarahkan langsung ke Live Chat
        $loginRes = $this->post(route('login.attempt'), [
            'email' => 'cs.chat@unpam.ac.id',
            'password' => 'password123',
        ]);
        $loginRes->assertRedirect(route('admin.live-chat.index'));

        // 2. Boleh mengakses Live Chat beserta aksi balas, ambil alih, poll, tutup
        $conversation = Conversation::create([
            'channel' => 'website',
            'external_user_id' => 'web-chat-role-1',
            'session_id' => 'web-chat-role-1',
            'user_name' => 'Mahasiswa Uji Chat',
            'status' => Conversation::STATUS_WAITING_ADMIN,
            'last_message_at' => now(),
        ]);

        $this->actingAs($adminChat)->get(route('admin.live-chat.index'))
            ->assertOk()
            ->assertDontSee('Ringkasan Tugas')
            ->assertDontSee('Informasi Kampus')
            ->assertDontSee('Pengingat Mahasiswa');

        $this->actingAs($adminChat)->post(route('admin.live-chat.take-over', $conversation))->assertRedirect();
        $this->actingAs($adminChat)->post(route('admin.live-chat.reply', $conversation), [
            'message' => 'Halo, dibalas oleh Admin Chat.',
        ])->assertRedirect();
        $this->actingAs($adminChat)->get(route('admin.live-chat.poll', $conversation))->assertOk();
        $this->actingAs($adminChat)->post(route('admin.live-chat.close', $conversation))->assertRedirect();

        // 3. Dilarang (403) mengakses seluruh modul selain Live Chat
        $this->actingAs($adminChat)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($adminChat)->get(route('admin.informations.index'))->assertForbidden();
        $this->actingAs($adminChat)->get(route('admin.reminders.index'))->assertForbidden();
        $this->actingAs($adminChat)->get(route('admin.students.index'))->assertForbidden();
        $this->actingAs($adminChat)->get(route('admin.settings.index'))->assertForbidden();
        $this->actingAs($adminChat)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_concurrent_login_is_rejected_when_active_session_exists_and_allowed_when_expired(): void
    {
        config(['session.driver' => 'database']);

        $admin = User::factory()->create([
            'email' => 'concurrent@unpam.ac.id',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        // Simulasikan sesi aktif milik perangkat lain di tabel sessions
        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            'id' => 'other-device-session-id-123',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'OtherBrowser',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ]);

        // 1. Login kedua dengan akun yang sama wajib ditolak
        $blockedRes = $this->post(route('login.attempt'), [
            'email' => 'concurrent@unpam.ac.id',
            'password' => 'password123',
        ]);
        $blockedRes->assertSessionHasErrors('email');
        $this->assertGuest();

        // 2. Jika sesi sebelumnya sudah kedaluwarsa, login diizinkan dan sesi lama dibersihkan
        \Illuminate\Support\Facades\DB::table('sessions')
            ->where('id', 'other-device-session-id-123')
            ->update(['last_activity' => now()->subMinutes(180)->timestamp]);

        $allowedRes = $this->post(route('login.attempt'), [
            'email' => 'concurrent@unpam.ac.id',
            'password' => 'password123',
        ]);
        $allowedRes->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseMissing('sessions', ['id' => 'other-device-session-id-123']);

        // 3. Logout menghapus seluruh sisa sesi akun di tabel sessions sehingga perangkat lain bisa login kembali
        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            'id' => 'stale-session-id-456',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'StaleBrowser',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ]);
        $this->actingAs($admin)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertDatabaseMissing('sessions', ['user_id' => $admin->id]);
    }

    public function test_admin_routes_and_logout_include_no_cache_security_headers(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        // Verifikasi route admin memuat security header no-cache
        $res = $this->actingAs($admin)->get(route('admin.dashboard'));
        $res->assertOk();
        $this->assertStringContainsString('no-cache', (string) $res->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $res->headers->get('Cache-Control'));
        $this->assertStringContainsString('must-revalidate', (string) $res->headers->get('Cache-Control'));
        $res->assertHeader('Pragma', 'no-cache');

        // Verifikasi logout menghancurkan sesi dan menyertakan header no-cache
        $logoutRes = $this->actingAs($admin)->post(route('logout'));
        $logoutRes->assertRedirect(route('login'));
        $this->assertStringContainsString('no-cache', (string) $logoutRes->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $logoutRes->headers->get('Cache-Control'));
        $logoutRes->assertHeader('Pragma', 'no-cache');
        $this->assertGuest();
    }

    public function test_superadmin_can_create_and_update_admin_chat_user(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        // 1. Tambah akun dengan role admin_chat
        $storeRes = $this->actingAs($superadmin)->post(route('admin.users.store'), [
            'name' => 'CS Live Chat 1',
            'email' => 'cs1@unpam.ac.id',
            'role' => User::ROLE_ADMIN_CHAT,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $storeRes->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'cs1@unpam.ac.id',
            'role' => User::ROLE_ADMIN_CHAT,
        ]);

        $chatUser = User::where('email', 'cs1@unpam.ac.id')->firstOrFail();

        // 2. Ubah role menjadi admin reguler, lalu kembalikan ke admin_chat
        $this->actingAs($superadmin)->put(route('admin.users.update', $chatUser), [
            'name' => 'CS Live Chat 1',
            'email' => 'cs1@unpam.ac.id',
            'role' => User::ROLE_ADMIN,
        ])->assertRedirect(route('admin.users.index'));
        $this->assertSame(User::ROLE_ADMIN, $chatUser->fresh()->role);

        $this->actingAs($superadmin)->put(route('admin.users.update', $chatUser), [
            'name' => 'CS Live Chat 1',
            'email' => 'cs1@unpam.ac.id',
            'role' => User::ROLE_ADMIN_CHAT,
        ])->assertRedirect(route('admin.users.index'));
        $this->assertSame(User::ROLE_ADMIN_CHAT, $chatUser->fresh()->role);
    }
}
