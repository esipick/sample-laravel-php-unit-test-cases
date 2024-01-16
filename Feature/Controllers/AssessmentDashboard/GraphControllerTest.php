<?php

namespace Feature\Controllers\Tasks;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardTaskSetsItem;
use App\Models\DashboardTaskSetTimeInterval;
use App\Models\DashboardTimeInterval;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class GraphControllerTest extends TestCase
{
    public function testGetGraphDataFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create([
            "dueAt" => null
        ]);

        $dashboard = Dashboard::factory()->for($customer)->create([
            'isColorTemplateSaved' => 1
        ]);

        $dashboardTimeInterval = DashboardTimeInterval::factory()->for($dashboard)->for($customer)->create([
            'visibleOnDashboard' => 1
        ]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($user)->for($task)->for($dashboard)->create([
            'status' => 'Active'
        ]);

        DashboardTaskSetTimeInterval::factory()->times(rand(2, 10))->for($task)->for($dashboardTaskSetsItem)->create([
            'status' => 'Active',            
            'taskSetID' => $dashboardTaskSetsItem->taskSetID,
            'timeIntervalID' => $dashboardTimeInterval->timeIntervalID
        ]);

        $apiURL = '/api/graph-data/'.$dashboard->dashboardID;
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertTrue(collect($response->json('data.allTimeIntervals'))->count() > 0);
    }

    public function testGetGraphDataFunctionCase2()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create([
            "dueAt" => null
        ]);

        $dashboard = Dashboard::factory()->for($customer)->create();
        $dashboardTimeInterval = DashboardTimeInterval::factory()->for($dashboard)->for($customer)->create([
            'visibleOnDashboard' => 1
        ]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($user)->for($task)->for($dashboard)->create([
            'status' => 'Active'
        ]);

        DashboardTaskSetTimeInterval::factory()->times(rand(2, 10))->for($task)->for($dashboardTaskSetsItem)->create([
            'status' => 'Active',            
            'taskSetID' => $dashboardTaskSetsItem->taskSetID,
            'timeIntervalID' => $dashboardTimeInterval->timeIntervalID
        ]);

        $apiURL = '/api/graph-data/'.$dashboard->dashboardID;
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertTrue(collect($response->json('data.allTimeIntervals'))->count() > 0);
    }

    public function testGetHeatMapDataFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create([
            "dueAt" => null
        ]);

        $dashboard = Dashboard::factory()->for($customer)->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($user)->for($task)->for($dashboard)->create();

        DashboardTaskSetTimeInterval::factory()->times(rand(2, 10))->for($task)->for($dashboardTaskSetsItem)->create([
            'taskSetID' => $dashboardTaskSetsItem->taskSetID
        ]);

        $apiURL = '/api/heat-map-data/'.$dashboard->dashboardID; //http_build_query($requestData);;
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertTrue(collect($response->json('data.functions'))->count() > 0);
    }

    public function testGetTimeIntervalInfoFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(["dueAt" => date("Y-m-d H:i:s")]);

        $dashboard = Dashboard::factory()->for($customer)->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($user)->for($task)->for($dashboard)->create();
        $dashboardTimeInterval = DashboardTimeInterval::factory()->for($dashboard)->for($customer)->create();
        
        DashboardTaskSetTimeInterval::factory()->times(rand(2, 10))->for($task)->for($dashboardTaskSetsItem)->create([
            'timeIntervalID' => $dashboardTimeInterval->timeIntervalID
        ]);
    
        $requestData = [
            'timeIntervalID' => $dashboardTimeInterval->timeIntervalID
        ];

        $apiURL = '/api/time-interval-info?'.http_build_query($requestData);;
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $dashboardTimeInterval->timeIntervalName;
        $responseArray = $response->json('data.timeInterval')[0];

        $this->assertEquals($requestArray, $responseArray);
    }
}
