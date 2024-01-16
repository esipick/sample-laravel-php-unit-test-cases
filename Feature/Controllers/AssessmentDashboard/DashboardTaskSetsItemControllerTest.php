<?php

namespace Feature\Controllers\AssessmentDashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategory;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class DashboardTaskSetsItemControllerTest extends TestCase
{
    public function testDashboardTaskSetsItemStore()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();
        DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $taskSet = Task::factory()->for($customer)->for($dashboard)->create(['isTaskSet' => 1]);

        $response = $this->withHeaders($headers)
            ->post('/api/dashboards/taskset-item', [
                'dashboardID' => $dashboard->dashboardID,
                'taskSetID' => $taskSet->taskID,
            ]);

        $response->assertStatus(200);

        $response->assertJsonStructure(['data' => ['dashboardID', 'taskID']]);

        $this->assertDatabaseHas('dashboard_tasksets_items', [
            'dashboardID' => $dashboard->dashboardID,
            'taskSetID' => $taskSet->taskID,
        ]);
    }
}
