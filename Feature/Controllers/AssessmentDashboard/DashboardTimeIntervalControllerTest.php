<?php

namespace Feature\Controllers\AssessmentDashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardTaskSetsItem;
use App\Models\DashboardTaskSetTimeInterval;
use App\Models\DashboardTimeInterval;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class DashboardTimeIntervalControllerTest extends TestCase
{
    public function testDashboardTimeIntervalIndex()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $DashboardTimeInterval = DashboardTimeInterval::factory()->for($customer)->for($dashboard)->create();
        DashboardTaskSetTimeInterval::factory()->for($DashboardTimeInterval, 'timeInterval')->for($task)->for($dashboardTaskSetsItem, 'dashboardTaskSetsItem')->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->get("/api/dashboard-interval?dashboardID={$dashboard->dashboardID}");

        $response->assertStatus(200);

        $response->assertJsonStructure(['data' => [['priority', 'customerID', 'dashboardID']]]);

    }

    public function testDashboardTimeIntervalStore()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->post('/api/dashboard-interval', [
                'dashboardID' => $dashboard->dashboardID,
            ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(['data' => ['priority', 'customerID', 'dashboardID']]);
    }

    public function testDashboardTimeIntervalUpdate()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardInterval = DashboardTimeInterval::factory()->for($customer)->for($dashboard)->create();
        DashboardTaskSetTimeInterval::factory()->for($dashboardInterval, 'timeInterval')->for($task)->for($dashboardTaskSetsItem, 'dashboardTaskSetsItem')->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->put("/api/dashboard-interval/{$dashboardInterval->timeIntervalID}", [
                'timeIntervalName' => 'TEst',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['priority', 'customerID', 'dashboardID']]);
    }

    public function testDashboardTimeIntervalDestroy()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardInterval = DashboardTimeInterval::factory()->for($customer)->for($dashboard)->create();
        DashboardTaskSetTimeInterval::factory()->for($dashboardInterval, 'timeInterval')->for($task)->for($dashboardTaskSetsItem, 'dashboardTaskSetsItem')->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->delete("/api/dashboard-interval/{$dashboardInterval->timeIntervalID}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('dashboard_time_intervals', ['timeIntervalID' => $dashboardInterval->timeIntervalID]);
    }

    public function testDashboardTimeIntervalUpdatePriority()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => 1]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        $dashboardInterval1 = DashboardTimeInterval::factory()->for($customer)->for($dashboard)->create();
        DashboardTaskSetTimeInterval::factory()->for($dashboardInterval1, 'timeInterval')->for($task)->for($dashboardTaskSetsItem, 'dashboardTaskSetsItem')->create();

        $dashboardInterval2 = DashboardTimeInterval::factory()->for($customer)->for($dashboard)->create();
        DashboardTaskSetTimeInterval::factory()->for($dashboardInterval2, 'timeInterval')->for($task)->for($dashboardTaskSetsItem, 'dashboardTaskSetsItem')->create();

        $dashboardInterval3 = DashboardTimeInterval::factory()->for($customer)->for($dashboard)->create();
        DashboardTaskSetTimeInterval::factory()->for($dashboardInterval3, 'timeInterval')->for($task)->for($dashboardTaskSetsItem, 'dashboardTaskSetsItem')->create();

        $headers = $this->authenticateUser($user);

        $timeIntervals = [
            ['timeIntervalID' => $dashboardInterval1->timeIntervalID, 'priority' => 3],
            ['timeIntervalID' => $dashboardInterval2->timeIntervalID, 'priority' => 1],
            ['timeIntervalID' => $dashboardInterval3->timeIntervalID, 'priority' => 2],
        ];

        $response = $this->withHeaders($headers)
            ->patch('/api/update-interval-priority', ['timeIntervals' => $timeIntervals]);
        $response->assertStatus(200);
    }
}
