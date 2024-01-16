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

class DashboardCategoryTaskItemTest extends TestCase
{
    public function testCreateDashboardCategoryTaskItem()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->for($customer)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardCategoryTaskItem = DashboardCategoryTaskItem::factory()->for($dashboardCategory)->for($task)->for($dashboardTaskSetsItem)->create();

        $this->assertInstanceOf(DashboardCategoryTaskItem::class, $dashboardCategoryTaskItem);
        $this->assertDatabaseHas('dashboard_category_tasks_items', ['dashboardCategoryTasksItemID' => $dashboardCategoryTaskItem->dashboardCategoryTasksItemID]);
    }

    public function testExistsScope()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->for($customer)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        DashboardCategoryTaskItem::factory()->for($dashboardCategory)->for($task)->for($dashboardTaskSetsItem)->create();

        $exists = DashboardCategoryTaskItem::exists(1, 1);

        $this->assertInstanceOf(DashboardCategoryTaskItem::class, $exists);
    }

    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->for($customer)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardCategoryTaskItem = DashboardCategoryTaskItem::factory()->for($dashboardCategory)->for($task)->for($dashboardTaskSetsItem)->create();

        $this->assertInstanceOf(Task::class, $dashboardCategoryTaskItem->task);
    }

    public function testDashboardCategoryRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->for($customer)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardCategoryTaskItem = DashboardCategoryTaskItem::factory()->for($dashboardCategory)->for($task)->for($dashboardTaskSetsItem)->create();

        $this->assertInstanceOf(DashboardCategory::class, $dashboardCategoryTaskItem->dashboardCategory);
    }

    public function testDashboardTasksetsItemRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->for($customer)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardCategoryTaskItem = DashboardCategoryTaskItem::factory()->for($dashboardCategory)->for($task)->for($dashboardTaskSetsItem)->create();

        $this->assertInstanceOf(DashboardTaskSetsItem::class, $dashboardCategoryTaskItem->dashboardTasksetsItem);
    }
}
