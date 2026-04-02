<?php

namespace Tests\Feature;

use App\Models\TPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontPackagesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_packages_page_renders_enabled_packages_from_database(): void
    {
        TPackage::query()->forceCreate([
            'name' => '专业版',
            'code' => 'pro',
            'price' => 1999,
            'year_price' => 19990,
            'features' => json_encode(['4x NVIDIA T4 GPU'], JSON_UNESCAPED_UNICODE),
            'sort' => 1,
            'status' => 1,
            'trial_days' => 7,
        ]);

        $response = $this->get('/packages');

        $response->assertOk()
            ->assertSee('专业版')
            ->assertSee('4x NVIDIA T4 GPU')
            ->assertSee('月度付费', false)
            ->assertSee('年度付费', false)
            ->assertSee('payment-modal', false)
            ->assertSee('/order/create', false)
            ->assertSee('/orders/', false);
    }
}
