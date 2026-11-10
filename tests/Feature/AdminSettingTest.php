<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_settings(): void
    {
        $response = $this->get(route('admin.settings.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_settings_page(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan Sistem');
        $response->assertSee('Identitas');
        $response->assertSee('Integrasi WhatsApp Gateway (Fonnte)');
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->create();

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
}
