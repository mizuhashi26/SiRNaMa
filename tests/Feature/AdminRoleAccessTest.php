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

    public function test_admin_auto_logs_out_after_15_minutes_idle_and_purges_session(): void
    {
        config([
            'session.driver' => 'database',
            'session.admin_idle_timeout' => 15,
            'session.admin_close_grace_seconds' => 60,
        ]);

        $admin = User::factory()->create([
            'email' => 'idle.admin@unpam.ac.id',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        // 1. Login mengisi timestamp aktivitas awal dan halaman admin merender timer client-side 15 menit + 60 detik tutup tab
        $this->post(route('login.attempt'), [
            'email' => 'idle.admin@unpam.ac.id',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $dashboardRes = $this->get(route('admin.dashboard'));
        $dashboardRes->assertOk();
        $dashboardRes->assertSee('IDLE_TIMEOUT_MS = 900000', false);
        $dashboardRes->assertSee('CLOSE_GRACE_MS = 60000', false);

        // 2. Aktivitas sebelum 15 menit (10 menit idle) masih diperbolehkan dan memperbarui timestamp
        $this->withSession([
            \App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_ACTIVITY_KEY => now()->subMinutes(10)->timestamp,
        ])->get(route('admin.dashboard'))->assertOk();
        $this->assertAuthenticatedAs($admin);

        // 3. Idle >= 15 menit memaksa logout, hapus sesi DB, dan alihkan ke halaman login dengan peringatan
        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            'id' => 'idle-expired-session-789',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'IdleBrowser',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->subMinutes(16)->timestamp,
        ]);

        $expiredRes = $this->withSession([
            \App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_ACTIVITY_KEY => now()->subMinutes(16)->timestamp,
        ])->get(route('admin.dashboard'));

        $expiredRes->assertRedirect(route('login', ['idle_timeout' => 1]));
        $expiredRes->assertSessionHas('warning');
        $this->assertStringContainsString('no-cache', (string) $expiredRes->headers->get('Cache-Control'));
        $this->assertGuest();
        $this->assertDatabaseMissing('sessions', ['user_id' => $admin->id]);

        // 4. Halaman login menampilkan pesan idle timeout 15 menit
        $this->get(route('login', ['idle_timeout' => 1]))
            ->assertOk()
            ->assertSee('tidak ada aktivitas selama 15 menit');
    }

    public function test_live_chat_poll_does_not_extend_idle_timeout_and_returns_401_when_expired(): void
    {
        config(['session.admin_idle_timeout' => 15]);

        $adminChat = User::factory()->create([
            'role' => User::ROLE_ADMIN_CHAT,
        ]);

        $conversation = Conversation::create([
            'channel' => 'website',
            'external_user_id' => 'web-idle-poll-1',
            'session_id' => 'web-idle-poll-1',
            'user_name' => 'Mahasiswa Idle',
            'status' => Conversation::STATUS_WAITING_ADMIN,
            'last_message_at' => now(),
        ]);

        $staleTimestamp = now()->subMinutes(10)->timestamp;

        // 1. Poll saat belum 15 menit: berhasil 200 tetapi TIDAK memperbarui timestamp idle
        $pollOk = $this->actingAs($adminChat)
            ->withSession([\App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_ACTIVITY_KEY => $staleTimestamp])
            ->withHeaders(['X-Background-Poll' => '1', 'Accept' => 'application/json'])
            ->get(route('admin.live-chat.poll', $conversation));

        $pollOk->assertOk();
        $this->assertSame(
            $staleTimestamp,
            session(\App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_ACTIVITY_KEY)
        );

        // 2. Poll setelah >= 15 menit idle: langsung 401 JSON + logout
        $pollExpired = $this->actingAs($adminChat)
            ->withSession([\App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_ACTIVITY_KEY => now()->subMinutes(16)->timestamp])
            ->withHeaders(['X-Background-Poll' => '1', 'Accept' => 'application/json'])
            ->get(route('admin.live-chat.poll', $conversation));

        $pollExpired->assertStatus(401)
            ->assertJsonStructure(['message', 'redirect']);
        $this->assertGuest();
    }

    public function test_concurrent_login_unlocks_after_15_minutes_even_if_session_lifetime_is_higher(): void
    {
        config([
            'session.driver' => 'database',
            'session.lifetime' => 120,
            'session.admin_idle_timeout' => 15,
        ]);

        $admin = User::factory()->create([
            'email' => 'concurrent15@unpam.ac.id',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            'id' => 'stale-after-20-mins',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'OtherBrowser',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->subMinutes(20)->timestamp,
        ]);

        $this->post(route('login.attempt'), [
            'email' => 'concurrent15@unpam.ac.id',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseMissing('sessions', ['id' => 'stale-after-20-mins']);
    }

    public function test_admin_closes_browser_auto_logs_out_after_60_seconds_and_stays_active_before_60_seconds(): void
    {
        config([
            'session.driver' => 'database',
            'session.admin_idle_timeout' => 15,
            'session.admin_close_grace_seconds' => 60,
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // 1. Beacon closing menandai sesi sedang ditutup (204) dan active ping membatalkannya
        $this->actingAs($admin)
            ->post(route('admin.session.closing'))
            ->assertNoContent();
        $this->assertNotNull(session(\App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_CLOSING_KEY));
        $this->assertNotNull(\Illuminate\Support\Facades\Cache::get(\App\Http\Controllers\AuthController::closingCacheKey($admin->id)));

        $this->actingAs($admin)
            ->postJson(route('admin.session.active'))
            ->assertOk();
        $this->assertNull(session(\App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_CLOSING_KEY));
        $this->assertNull(\Illuminate\Support\Facades\Cache::get(\App\Http\Controllers\AuthController::closingCacheKey($admin->id)));

        // 2. Kembali masuk dalam 45 detik (< 60 detik): tetap login
        $this->actingAs($admin)
            ->withSession([
                \App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_ACTIVITY_KEY => now()->timestamp,
                \App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_CLOSING_KEY => now()->subSeconds(45)->timestamp,
            ])
            ->get(route('admin.dashboard'))
            ->assertOk();
        $this->assertAuthenticatedAs($admin);

        // 3. Kembali masuk setelah >= 60 detik (65 detik) penutupan: otomatis logout + hapus sesi DB
        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            'id' => 'closed-browser-session-111',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'ClosedBrowser',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->subSeconds(65)->timestamp,
        ]);

        $closedExpiredRes = $this->actingAs($admin)
            ->withSession([
                \App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_ACTIVITY_KEY => now()->timestamp,
                \App\Http\Middleware\EnforceAdminIdleTimeout::SESSION_CLOSING_KEY => now()->subSeconds(65)->timestamp,
            ])
            ->get(route('admin.dashboard'));

        $closedExpiredRes->assertRedirect(route('login', ['close_timeout' => 1]));
        $closedExpiredRes->assertSessionHas('warning');
        $this->assertGuest();
        $this->assertDatabaseMissing('sessions', ['user_id' => $admin->id]);

        $this->get(route('login', ['close_timeout' => 1]))
            ->assertOk()
            ->assertSee('halaman admin ditutup lebih dari 60 detik');
    }

    public function test_concurrent_login_unlocks_after_60_seconds_when_previous_session_closed_browser(): void
    {
        config([
            'session.driver' => 'database',
            'session.lifetime' => 15,
            'session.admin_idle_timeout' => 15,
            'session.admin_close_grace_seconds' => 60,
        ]);

        $admin = User::factory()->create([
            'email' => 'closegrace@unpam.ac.id',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            'id' => 'device-a-closed-sess',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'DeviceA',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->subSeconds(30)->timestamp,
        ]);

        // 1. Baru ditutup 30 detik (< 60 detik): login kedua masih ditolak
        \Illuminate\Support\Facades\Cache::put(
            \App\Http\Controllers\AuthController::closingCacheKey($admin->id),
            ['session_id' => 'device-a-closed-sess', 'closed_at' => now()->subSeconds(30)->timestamp],
            300
        );

        $this->post(route('login.attempt'), [
            'email' => 'closegrace@unpam.ac.id',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        // 2. Sudah ditutup >= 60 detik (65 detik): login kedua langsung diizinkan & sesi lama dihapus
        \Illuminate\Support\Facades\Cache::put(
            \App\Http\Controllers\AuthController::closingCacheKey($admin->id),
            ['session_id' => 'device-a-closed-sess', 'closed_at' => now()->subSeconds(65)->timestamp],
            300
        );

        $this->post(route('login.attempt'), [
            'email' => 'closegrace@unpam.ac.id',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseMissing('sessions', ['id' => 'device-a-closed-sess']);
    }
}
