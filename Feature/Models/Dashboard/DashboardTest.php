<?php

namespace Feature\Models\Dashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategory;
use App\Models\DashboardColor;
use App\Models\DashboardTaskSetsItem;
use App\Models\DashboardTimeInterval;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function testCreateDashboard()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $this->assertInstanceOf(Dashboard::class, $dashboard);
        $this->assertDatabaseHas('dashboards', ['dashboardID' => $dashboard->dashboardID]);
    }

    public function testUserRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $this->assertInstanceOf(User::class, $dashboard->user);
    }

    public function testLocationRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $this->assertInstanceOf(Location::class, $dashboard->location);
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $this->assertInstanceOf(Customer::class, $dashboard->customer);
    }

    public function testDashboardTimeIntervalsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        DashboardTimeInterval::factory()->for($dashboard)->for($customer)->count(5)->create();
        $this->assertInstanceOf(DashboardTimeInterval::class, $dashboard->dashboardTimeIntervals->first());
    }

    public function testDashboardColorsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        DashboardColor::factory()->for($dashboard)->count(5)->create();
        $this->assertInstanceOf(DashboardColor::class, $dashboard->dashboardColors->first());
    }

    public function testDashboardCategoriesRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        DashboardCategory::factory()->for($dashboard)->for($customer)->count(5)->create();
        $this->assertInstanceOf(DashboardCategory::class, $dashboard->dashboardCategories->first());
    }

    public function testDashboardTasksetsItemsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->for($user)->create();
        DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->count(5)->create();

        $this->assertInstanceOf(DashboardTaskSetsItem::class, $dashboard->dashboardTasksetsItems->first());
    }

    public function testTaskSetsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        Task::factory()->for($customer)->for($user)->for($dashboard)->count(5)->create(['isTaskSet' => true]);

        $this->assertInstanceOf(Task::class, $dashboard->taskSets->first());
    }

    public function testActiveScope()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        Dashboard::factory()->for($customer)->for($location)->for($user)->create(['status' => 'Published']);
        Dashboard::factory()->for($customer)->for($location)->for($user)->create(['status' => 'Deleted']);

        $activeDashboards = Dashboard::active()->get();

        $this->assertCount(1, $activeDashboards);
    }

    public function testDashboardStatusAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $draftedDashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create(['publishedAt' => null, 'archivedAt' => null]);
        $publishedDashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create(['publishedAt' => now()]);
        $archivedDashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create(['archivedAt' => now()]);

        $this->assertEquals('Drafted', $draftedDashboard->dashboardStatus);
        $this->assertEquals('Published', $publishedDashboard->dashboardStatus);
        $this->assertEquals('Archived', $archivedDashboard->dashboardStatus);
    }
}
