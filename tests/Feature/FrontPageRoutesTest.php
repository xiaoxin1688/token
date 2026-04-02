<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontPageRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Neural Nexus', false)
            ->assertSee('星云算力', false)
            ->assertSee('产品', false);
    }

    public function test_packages_page_is_accessible(): void
    {
        $response = $this->get('/packages');

        $response->assertOk();
    }
}
