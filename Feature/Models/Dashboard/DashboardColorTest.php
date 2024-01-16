<?php

namespace Feature\Models\Dashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardColor;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class DashboardColorTest extends TestCase
{
    public function testCreateDashboardColor()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardColor = DashboardColor::factory()->for($dashboard)->create();

        $this->assertInstanceOf(DashboardColor::class, $dashboardColor);
        $this->assertDatabaseHas('dashboard_colors', ['colorID' => $dashboardColor->colorID]);
    }

    public function testDashboardRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardColor = DashboardColor::factory()->for($dashboard)->create();
        $this->assertInstanceOf(Dashboard::class, $dashboardColor->dashboard);
    }
}
