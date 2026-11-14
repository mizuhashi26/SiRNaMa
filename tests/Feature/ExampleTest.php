<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_custom_404_page_has_navbar_and_back_link(): void
    {
        foreach (['/halaman-acak-tidak-tersedia-404', '/json', '/composer.json', '/.env', '/.git/HEAD'] as $path) {
            $response = $this->get($path);

            $response->assertStatus(404);
            $response->assertSee('Semua Informasi');
            $response->assertSee('Akademik');
            $response->assertSee('Info Pembayaran');
            $response->assertSee('Ke Beranda Informasi');
            $response->assertSee('Kembali Sebelumnya');
        }
    }

    public function test_throttling_protects_public_endpoints_from_rate_abuse(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->get('/login');
        }

        // Request ke-61 dalam kurun 1 menit harus ditolak dengan HTTP 429 (Too Many Requests)
        $response = $this->get('/login');
        $response->assertStatus(429);
    }
}
