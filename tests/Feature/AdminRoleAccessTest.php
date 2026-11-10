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
}
