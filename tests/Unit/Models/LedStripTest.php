<?php

namespace Tests\Unit\Models;

use App\Models\LedStrip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedStripTest extends TestCase
{
    use RefreshDatabase;

    public function test_power_class_attribute_is_warning_when_power_supply_less_than_power_necessary()
    {
        $length = 100;
        $powerNecessary = $length * 0.02 * 3;
        $powerSupply = $powerNecessary - 10;
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertEquals('warning', $ledStrip->power_class);
    }

    public function test_power_class_attribute_is_null_when_power_supply_equal_power_necessary()
    {
        $length = 100;
        $powerNecessary = $length * 0.02 * 3;
        $powerSupply = $powerNecessary;
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertNull($ledStrip->power_class);
    }

    public function test_power_class_attribute_is_null_when_power_supply_greater_than_power_necessary()
    {
        $length = 100;
        $powerNecessary = $length * 0.02 * 3;
        $powerSupply = $powerNecessary + 10;
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertNull($ledStrip->power_class);
    }

    public function test_limiting_factor_attribute_for_power_supply_exactly_for_length()
    {
        $length = 100;
        $powerSupply = $length * 0.02 * 3;
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertEquals(1, $ledStrip->limiting_factor);
    }

    public function test_limiting_factor_attribute_for_power_supply_above_necessary_for_length()
    {
        $length = 100;
        $powerSupply = $length * 0.02 * 3 * 2;
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertEquals(1, $ledStrip->limiting_factor);
    }

    public function test_limiting_factor_attribute_for_power_supply_at_half_for_length()
    {
        $length = 100;
        $powerSupply = $length * 0.02 * 3 / 2;
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertEquals(0.5, $ledStrip->limiting_factor);
    }

    public function test_limiting_factor_attribute_for_power_supply_at_third_for_length()
    {
        $length = 100;
        $powerSupply = $length * 0.02 * 3 / 3;
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertEquals(0.33, $ledStrip->limiting_factor);
    }

    public function test_getLimitedColor_for_power_supply_above_given_color()
    {
        $length = 100;
        $powerSupply = $length * 0.02 * 3;
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertEquals('#7f7f7f', $ledStrip->getLimitedColor('#7f7f7f'));
    }

    public function test_getLimitedColor_for_power_supply_exactly_necessary_for_given_color()
    {
        $length = 100;
        $powerSupply = $length * 0.02 * 3;
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertEquals('#ffffff', $ledStrip->getLimitedColor('#ffffff'));
        $this->assertEquals('#555555', $ledStrip->getLimitedColor('#555555'));
    }

    public function test_getLimitedColor_for_half_power_supply_for_given_color()
    {
        $length = 100;
        $powerSupply = intval(($length * 0.02 * 3) / 2);
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertEquals('#7f7f7f', $ledStrip->getLimitedColor('#ffffff'));
    }

    public function test_getLimitedColor_for_third_power_supply_for_given_color()
    {
        $length = 100;
        $powerSupply = intval(($length * 0.02 * 3) / 3);
        $ledStrip = LedStrip::factory()->create([
            'length' => $length,
            'power_supply' => $powerSupply,
        ]);

        $this->assertEquals('#545454', $ledStrip->getLimitedColor('#ffffff'));
    }
}
