<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\CampusInformation;
use App\Models\User;
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

    public function test_public_pages_render_seo_and_open_graph_meta_tags(): void
    {
        $home = $this->get(route('public.index'));
        $home->assertOk();
        $home->assertSee('<meta name="description" content="SiRNaMa - Sistem Informasi Reminder dan Layanan Mahasiswa berbasis Web dan WhatsApp Gateway">', false);
        $home->assertSee('<meta name="keywords" content="SiRNaMa, Sistem Informasi Layanan Mahasiswa, CRM Mahasiswa, Helpdesk Kampus">', false);
        $home->assertSee('<meta name="robots" content="index, follow">', false);
        $home->assertSee('<meta property="og:title"', false);
        $home->assertSee('<meta property="og:description"', false);
        $home->assertSee('<meta property="og:url"', false);
        $home->assertSee('<meta property="og:type" content="website">', false);

        $info = CampusInformation::create([
            'title' => 'Jadwal KRS & Pembayaran',
            'slug' => 'jadwal-krs-pembayaran',
            'category' => 'akademik',
            'summary' => 'Panduan lengkap pengisian KRS semester berjalan.',
            'content' => '<p>Detail informasi KRS.</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $detail = $this->get(route('public.show', $info->slug));
        $detail->assertOk();
        $detail->assertSee('<meta name="description" content="Panduan lengkap pengisian KRS semester berjalan.">', false);
        $detail->assertSee('<meta property="og:title" content="Jadwal KRS &amp; Pembayaran - Informasi Mahasiswa UNPAM">', false);
        $detail->assertSee('<meta property="og:type" content="article">', false);
    }

    public function test_login_and_admin_pages_render_noindex_robots_meta_tag(): void
    {
        $login = $this->get(route('login'));
        $login->assertOk();
        $login->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $dashboard = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_dynamic_sitemap_lists_public_routes_and_published_articles_only(): void
    {
        CampusInformation::create([
            'title' => 'Informasi Terbit',
            'slug' => 'informasi-terbit',
            'category' => 'pengumuman',
            'summary' => 'Ringkasan terbit',
            'content' => 'Konten terbit',
            'is_published' => true,
            'published_at' => now(),
        ]);

        CampusInformation::create([
            'title' => 'Informasi Draf',
            'slug' => 'informasi-draf',
            'category' => 'pengumuman',
            'summary' => 'Ringkasan draf',
            'content' => 'Konten draf',
            'is_published' => false,
            'published_at' => null,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
        $response->assertSee('<priority>1.0</priority>', false);
        $response->assertSee('<lastmod>', false);
        $response->assertSee(route('public.index'), false);
        $response->assertSee(route('public.about'), false);
        $response->assertSee(route('public.contact'), false);
        $response->assertSee('/informasi/informasi-terbit', false);
        $response->assertDontSee('/informasi/informasi-draf', false);
    }

    public function test_static_robots_and_google_verification_files_exist(): void
    {
        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);

        $robots = file_get_contents($robotsPath) ?: '';
        $this->assertStringContainsString('User-agent: *', $robots);
        $this->assertStringContainsString('Allow: /', $robots);
        $this->assertStringContainsString('Disallow: /dashboard', $robots);
        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringContainsString('Disallow: /login', $robots);
        $this->assertStringContainsString('Disallow: /api', $robots);
        $this->assertStringContainsString('Sitemap: https://sirnama.vercel.app/sitemap.xml', $robots);

        $googleFile = public_path('googlecbb8cd52a43470c8.html');
        $this->assertFileExists($googleFile);
        $this->assertSame('google-site-verification: googlecbb8cd52a43470c8.html', trim((string) file_get_contents($googleFile)));
    }
}

