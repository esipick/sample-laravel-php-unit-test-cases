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

class DashboardTaskSetCategoryTest extends TestCase
{
    public function testCreateDashboardTaskSetCategory()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardTaskSetCategory = DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($dashboardCategory)->create();

        $this->assertInstanceOf(DashboardTaskSetCategory::class, $dashboardTaskSetCategory);
        $this->assertDatabaseHas('dashboard_tasksets_categories', ['dashboardTasksetsCategoryID' => $dashboardTaskSetCategory->dashboardTasksetsCategoryID]);
    }

    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardTaskSetCategory = DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($dashboardCategory)->create();

        $this->assertInstanceOf(Task::class, $dashboardTaskSetCategory->task);
    }

    public function testDashboardCategoryRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardTaskSetCategory = DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($dashboardCategory)->create();

        $this->assertInstanceOf(DashboardCategory::class, $dashboardTaskSetCategory->dashboardCategory);
    }

    public function testDashboardCategoryTaskItemsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardTaskSetCategory = DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($dashboardCategory)->create();
        DashboardCategoryTaskItem::factory()->for($dashboardCategory)->for($task)->for($dashboardTaskSetsItem)->create();

        $this->assertInstanceOf(DashboardCategoryTaskItem::class, $dashboardTaskSetCategory->dashboardCategoryTaskItems->first());
    }

    public function testDashboardTasksetsItemRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardTaskSetCategory = DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($dashboardCategory)->create();

        $this->assertInstanceOf(DashboardTaskSetsItem::class, $dashboardTaskSetCategory->dashboardTasksetsItem);
    }
}
