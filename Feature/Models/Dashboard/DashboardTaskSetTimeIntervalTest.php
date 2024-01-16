<?php

namespace Feature\Models\Dashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardTaskSetsItem;
use App\Models\DashboardTaskSetTimeInterval;
use App\Models\DashboardTimeInterval;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class DashboardTaskSetTimeIntervalTest extends TestCase
{
    public function testCreateDashboardTaskSetCategory()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardTaskSetTimeInterval = DashboardTaskSetTimeInterval::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTaskSetsItem')->create();

        $this->assertInstanceOf(DashboardTaskSetTimeInterval::class, $dashboardTaskSetTimeInterval);

    }

    public function testTimeIntervalRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $DashboardTimeInterval = DashboardTimeInterval::factory()->for($customer)->for($dashboard)->create();
        $dashboardTaskSetTimeInterval = DashboardTaskSetTimeInterval::factory()->for($DashboardTimeInterval, 'timeInterval')->for($task)->for($dashboardTaskSetsItem, 'dashboardTaskSetsItem')->create();

        $this->assertInstanceOf(DashboardTimeInterval::class, $dashboardTaskSetTimeInterval->timeInterval);
    }

    public function testDashboardTaskSetsItemRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardTaskSetTimeInterval = DashboardTaskSetTimeInterval::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTaskSetsItem')->create();

        $this->assertInstanceOf(DashboardTaskSetsItem::class, $dashboardTaskSetTimeInterval->dashboardTaskSetsItem);
    }

    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardTaskSetTimeInterval = DashboardTaskSetTimeInterval::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTaskSetsItem')->create();
        $this->assertInstanceOf(Task::class, $dashboardTaskSetTimeInterval->task);
    }
}
