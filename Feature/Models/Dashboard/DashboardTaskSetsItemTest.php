<?php

namespace Feature\Models\Dashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategory;
use App\Models\DashboardCategoryTaskItem;
use App\Models\DashboardTaskSetsItem;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class DashboardTaskSetsItemTest extends TestCase
{
    public function testCreateDashboardTaskSetsItem()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();

        $this->assertInstanceOf(DashboardTaskSetsItem::class, $dashboardTaskSetsItem);
        $this->assertDatabaseHas('dashboard_tasksets_items', ['dashboardItemID' => $dashboardTaskSetsItem->dashboardItemID]);
    }

    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();

        $this->assertInstanceOf(Task::class, $dashboardTaskSetsItem->task);
    }

    public function testDashboardRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();

        $this->assertInstanceOf(Dashboard::class, $dashboardTaskSetsItem->dashboard);
    }

    public function testDashboardCategoryTaskItemsRelationship()
    {

        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        DashboardCategoryTaskItem::factory()->for($dashboardCategory)->for($task)->for($dashboardTaskSetsItem)->count(6)->create();

        $this->assertInstanceOf(DashboardCategoryTaskItem::class, $dashboardTaskSetsItem->dashboardCategoryTaskItems->first());
    }

    public function testUserRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $this->assertInstanceOf(User::class, $dashboardTaskSetsItem->user);
    }
}
