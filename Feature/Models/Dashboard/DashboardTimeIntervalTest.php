<?php

namespace Feature\Models\Dashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardTimeInterval;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class DashboardTimeIntervalTest extends TestCase
{
    public function testCreateDashboardTimeInterval()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardTimeInterval = DashboardTimeInterval::factory()->for($dashboard)->for($customer)->create();

        $this->assertInstanceOf(DashboardTimeInterval::class, $dashboardTimeInterval);
        $this->assertDatabaseHas('dashboard_time_intervals', ['timeIntervalID' => $dashboardTimeInterval->timeIntervalID]);
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardTimeInterval = DashboardTimeInterval::factory()->for($dashboard)->for($customer)->create();

        $this->assertInstanceOf(Customer::class, $dashboardTimeInterval->customer);
    }

    public function testDashboardRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardTimeInterval = DashboardTimeInterval::factory()->for($dashboard)->for($customer)->create();

        $this->assertInstanceOf(Dashboard::class, $dashboardTimeInterval->dashboard);
    }
}
