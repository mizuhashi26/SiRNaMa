<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_admin_dashboard_and_portal_returns_200(): void
    {
        $this->get('/')->assertRedirect('/admin');
        $this->get('/portal')->assertStatus(200);
    }
}
