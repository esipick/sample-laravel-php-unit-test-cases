<?php

namespace Feature\Models\Dashboard;

use App\Models\DashboardDefaultColor;
use Tests\TestCase;

class DashboardDefaultColorTest extends TestCase
{
    public function testCreateDashboardDefaultColor()
    {
        $dashboardDefaultColor = DashboardDefaultColor::factory()->create([
            'from' => '0',
            'to' => '100',
            'colorCode' => '#FF5733',
            'colorLabel' => 'Red',
        ]);

        $this->assertInstanceOf(DashboardDefaultColor::class, $dashboardDefaultColor);
        $this->assertDatabaseHas('dashboard_default_colors', ['colorID' => $dashboardDefaultColor->colorID]);
    }
}
