<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        @unlink(AppSetting::persistentFilePath());
        AppSetting::clearSettingCache();
        parent::tearDown();
    }

    public function test_guest_cannot_access_settings(): void
    {
        $response = $this->get(route('admin.settings.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_admin_forbidden_from_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_can_view_settings_page(): void
    {
        $admin = User::factory()->superadmin()->create();

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan Sistem');
        $response->assertSee('Identitas');
        $response->assertSee('Integrasi WhatsApp Gateway (Fonnte)');
    }

    public function test_superadmin_can_update_settings(): void
    {
        $admin = User::factory()->superadmin()->create();

        $payload = [
            'app_institution_name' => 'Universitas Pamulang Baru',
            'app_logo_text' => 'UNPAM JAYA',
            'app_tagline' => 'Tagline Baru Keren',
            'app_footer_note' => 'Hak Cipta 2026 UNPAM Baru',
            'fonnte_token' => 'fonnte-secret-token-12345',
            'fonnte_api_url' => 'https://api.fonnte.com/send',
            'admin_contact_phone' => '628999888777',
        ];

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), $payload);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('Universitas Pamulang Baru', AppSetting::getValue('app_institution_name'));
        $this->assertEquals('UNPAM JAYA', AppSetting::getValue('app_logo_text'));
        $this->assertEquals('fonnte-secret-token-12345', AppSetting::getValue('fonnte_token'));
        $this->assertEquals('628999888777', AppSetting::getValue('admin_contact_phone'));
    }

    public function test_settings_persist_across_database_reset(): void
    {
        $admin = User::factory()->superadmin()->create();

        // 1. Simpan pengaturan API
        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'app_institution_name' => 'Universitas Pamulang Mandiri',
            'app_tagline' => 'Tagline Mandiri',
            'fonnte_token' => 'my-persistent-token-999',
            'fonnte_api_url' => 'https://api.fonnte.com/send',
        ]);

        // 2. Simulasi database ter-reset (semua baris app_settings terhapus)
        AppSetting::query()->delete();
        AppSetting::clearSettingCache();
        $this->assertEquals(0, AppSetting::count());

        // 3. AppSetting::getValue tetap berhasil membaca token dari persistent storage
        $this->assertEquals('my-persistent-token-999', AppSetting::getValue('fonnte_token'));

        // 4. Simulasi Seeder berjalan setelah reset database
        AppSetting::restorePersistentToDatabase();
        $this->assertDatabaseHas('app_settings', [
            'key' => 'fonnte_token',
            'value' => 'my-persistent-token-999',
        ]);
    }

    public function test_superadmin_can_update_web_colors_and_admin_name(): void
    {
        $admin = User::factory()->superadmin()->create(['name' => 'Admin Lama']);

        $payload = [
            'app_institution_name' => 'Universitas Pamulang',
            'app_tagline' => 'Pusat Informasi dan Layanan Mahasiswa',
            'theme_primary_color' => '#1E3A8A',
            'theme_accent_color' => '#F59E0B',
            'admin_name' => 'Pak Bambang Pamungkas',
        ];

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), $payload);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('#1e3a8a', AppSetting::getValue('theme_primary_color'));
        $this->assertEquals('#f59e0b', AppSetting::getValue('theme_accent_color'));
        $this->assertEquals('Pak Bambang Pamungkas', $admin->fresh()->name);
        $this->assertEquals('Pak Bambang Pamungkas', AppSetting::getValue('admin_name'));
    }

    public function test_invalid_color_format_is_rejected(): void
    {
        $admin = User::factory()->superadmin()->create();

        $payload = [
            'app_institution_name' => 'Universitas Pamulang',
            'theme_primary_color' => 'bukan-hex-color',
        ];

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), $payload);

        $response->assertSessionHasErrors(['theme_primary_color']);
    }

    public function test_chatbot_reflects_updated_campus_name_across_channels(): void
    {
        $admin = User::factory()->superadmin()->create();

        // 1. Ubah nama institusi & logo text di pengaturan admin
        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'app_institution_name' => 'Institut Teknologi Bandung',
            'app_logo_text' => 'ITB',
            'app_tagline' => 'In Harmonia Progressio',
        ]);

        AppSetting::clearSettingCache();

        // 2. Chatbot Web (sesi awal)
        $webRes = $this->getJson('/chat/session');
        $webRes->assertOk();
        $this->assertStringContainsString('Institut Teknologi Bandung', $webRes->json('messages.0.message'));
        $this->assertSame('Chatbot SiRNaMa ITB', $webRes->json('messages.0.sender_name'));

        // 3. Chatbot Web pilih Menu 1
        $menu1Res = $this->postJson('/chat/message', ['message' => '1']);
        $menu1Res->assertOk();
        $this->assertStringContainsString('INFORMASI KAMPUS ITB', collect($menu1Res->json('messages'))->last()['message']);

        // 4. Chatbot WhatsApp (Webhook Fonnte)
        $waRes = $this->postJson('/api/webhook/fonnte', [
            'sender' => '089911223344',
            'message' => 'menu',
            'name' => 'Ahmad Mahasiswa',
        ]);
        $waRes->assertOk();
        $this->assertStringContainsString('Institut Teknologi Bandung', $waRes->json('bot_reply'));
    }
}
