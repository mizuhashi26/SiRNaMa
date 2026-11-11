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
        $response = $this->get('/halaman-acak-tidak-tersedia-404');

        $response->assertStatus(404);
        $response->assertSee('Semua Informasi');
        $response->assertSee('Akademik');
        $response->assertSee('Info Pembayaran');
        $response->assertSee('Ke Beranda Informasi');
        $response->assertSee('Kembali Sebelumnya');
    }
}
