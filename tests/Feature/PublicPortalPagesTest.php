<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPortalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_loads_successfully_with_content(): void
    {
        $response = $this->get(route('public.about'));

        $response->assertOk();
        $response->assertSee('Tentang CRM Layanan Mahasiswa');
        $response->assertSee('Mewujudkan Layanan Informasi Mahasiswa');
        $response->assertSee('Empat Pilar Utama CRM Layanan');
        $response->assertSee('Portal Informasi Satu Pintu');
        $response->assertSee('Reminder WhatsApp Terjadwal');
        $response->assertSee('Chatbot Berbasis Menu');
        $response->assertSee('Live Chat Admin Terpusat');
    }

    public function test_contact_page_loads_with_bot_number_and_official_unpam_info(): void
    {
        AppSetting::setValue('admin_contact_phone', '628999111222', 'general', 'Nomor Bot');

        $response = $this->get(route('public.contact'));

        $response->assertOk();
        $response->assertSee('WhatsApp Bot Layanan Mahasiswa');
        $response->assertSee('wa.me/628999111222', false);
        $response->assertSee('Kontak Resmi Universitas Pamulang');
        $response->assertSee('Kampus 1 UNPAM (Pusat Pamulang)');
        $response->assertSee('Kampus 2 UNPAM (Viktor Serpong)');
        $response->assertSee('Kampus 3 UNPAM (Witana Harja)');
        $response->assertSee('info@unpam.ac.id');
        $response->assertSee('(021) 7412566');
    }
}

