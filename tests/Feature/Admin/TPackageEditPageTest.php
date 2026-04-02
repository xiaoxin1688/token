<?php

namespace Tests\Feature\Admin;

use App\Admin\Controllers\TPackageController;
use App\Models\TPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TPackageEditPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_edit_form_renders_when_features_are_cast_to_array(): void
    {
        $package = TPackage::query()->create([
            'name' => '专业版',
            'code' => 'pro',
            'price' => '99.00',
            'year_price' => '999.00',
            'features' => ['专属客服', '多节点支持'],
            'sort' => 1,
            'status' => 1,
            'trial_days' => 7,
        ]);

        $controller = new class extends TPackageController
        {
            public function exposedForm()
            {
                return $this->form();
            }
        };

        $html = $controller->exposedForm()->edit($package->id)->render();

        $this->assertStringContainsString('name="features"', $html);
        $this->assertStringContainsString('专属客服', $html);
        $this->assertStringContainsString('多节点支持', $html);
    }

    public function test_features_are_formatted_for_multiline_textarea(): void
    {
        $formatted = TPackage::formatFeaturesForTextarea(['专属客服', '多节点支持']);

        $this->assertSame("专属客服\n多节点支持", $formatted);
    }

    public function test_features_input_is_normalized_from_multiline_text(): void
    {
        $normalized = TPackage::normalizeFeaturesInput("专属客服\n\n 多节点支持 \n");

        $this->assertSame(['专属客服', '多节点支持'], $normalized);
    }
}
