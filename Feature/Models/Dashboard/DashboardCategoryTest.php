<?php

namespace Feature\Models\Dashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategory;
use App\Models\DashboardCategoryTaskItem;
use App\Models\DashboardTaskSetCategory;
use App\Models\DashboardTaskSetsItem;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class DashboardCategoryTest extends TestCase
{
    public function testCreateDashboardCategory()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();

        $this->assertInstanceOf(DashboardCategory::class, $dashboardCategory);
        $this->assertDatabaseHas('dashboard_categories', ['categoryID' => $dashboardCategory->categoryID]);
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $this->assertInstanceOf(Customer::class, $dashboardCategory->customer);
    }

    public function testUserRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($user)->for($dashboard)->create();
        $this->assertInstanceOf(User::class, $dashboardCategory->user);
    }

    public function testDashboardRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($user)->for($dashboard)->create();

        $this->assertInstanceOf(Dashboard::class, $dashboardCategory->dashboard);
    }

    public function testTaskSetGroupsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($dashboardCategory)->create();

        $this->assertInstanceOf(DashboardTaskSetCategory::class, $dashboardCategory->taskSetGroups->first());
    }

    public function testDashboardCategoryTaskItemsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        DashboardCategoryTaskItem::factory()->for($dashboardCategory)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($task)->create();

        $this->assertInstanceOf(DashboardCategoryTaskItem::class, $dashboardCategory->dashboardCategoryTaskItems->first());
    }
}
