<?php

namespace Feature\Controllers\Locations;

use App\Enum\ColorType;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\Timezone;
use App\Models\User;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    public function testIndexFunctionCasePerPage()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location1 = Location::factory()->times(rand(1, 5))->for($customer)->create();
        $location2 = Location::factory()->times(rand(1, 5))->for($customer)->create();
        $location3 = Location::factory()->times(rand(1, 5))->for($customer)->create();

        $requestData = ['perPage' => 3];

        $apiURL = '/api/locations?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $requestData['perPage'];
        $responseArray = $response->json('data.perPage');

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseSearch()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $search = 'abcdefghij';
        $location1 = Location::factory()->times(rand(1, 5))->for($customer)->create([
            'locationName' => \Str::random(5),
        ]);

        $location2 = Location::factory()->times(rand(1, 5))->for($customer)->create([
            'locationName' => \Str::random(5).$search,
        ]);

        $location3 = Location::factory()->times(rand(1, 5))->for($customer)->create([
            'locationName' => \Str::random(5),
        ]);

        $requestData = [
            'search' => $search,
        ];

        $apiURL = '/api/locations?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $location2->pluck('locationID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('locationID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOrderBy()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location1 = Location::factory()->times(rand(1, 5))->for($customer)->create();
        $location2 = Location::factory()->times(rand(1, 5))->for($customer)->create();
        $location3 = Location::factory()->times(rand(1, 5))->for($customer)->create();

        $requestData = [
            'orderBy' => 'desc',
            'orderByField' => 'locationID',
        ];

        $apiURL = '/api/locations?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = collect(array_merge($location2->toArray(), $location1->toArray(), $location3->toArray()))->pluck('locationID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('locationID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseDashboardId()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location1 = Location::factory()->for($customer)->create();
        $location2 = Location::factory()->for($customer)->create();
        $location3 = Location::factory()->for($customer)->create();

        $dashboard = Dashboard::factory()->for($customer)->for($location2)->create();

        $requestData = [
            'dashboardID' => $dashboard->dashboardID,
        ];

        $apiURL = '/api/locations?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $location2->toArray();
        $responseArray = collect($response->json('data.data'))->toArray();

        $requestArray = [
            'locationID' => $requestArray['locationID'],
            'locationName' => $requestArray['locationName'],
            'locationSchedulerActive' => $requestArray['locationSchedulerActive'],
            'customerID' => $requestArray['customerID'],
        ];

        $responseArray = [
            'locationID' => $responseArray[0]['locationID'],
            'locationName' => $responseArray[0]['locationName'],
            'locationSchedulerActive' => $responseArray[0]['locationSchedulerActive'],
            'customerID' => $responseArray[0]['customerID'],
        ];

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseAssignedLocations()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create([
            'userType' => null,
        ]);
        $headers = $this->authenticateUser($user);

        $location1 = Location::factory()->for($customer)->create();
        $location2 = Location::factory()->for($customer)->create();
        $location3 = Location::factory()->for($customer)->create();

        $profile = Profile::factory()->for($customer)->create();
        Security::factory()->for($profile)->for($user)->for($location2)->create();

        $requestData = [
            'assignedLocations' => true,
        ];

        $apiURL = '/api/locations?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $location2->toArray();
        $responseArray = collect($response->json('data.data'))->toArray();

        $requestArray = [
            'locationID' => $requestArray['locationID'],
            'locationName' => $requestArray['locationName'],
            'locationSchedulerActive' => $requestArray['locationSchedulerActive'],
            'customerID' => $requestArray['customerID'],
        ];

        $responseArray = [
            'locationID' => $responseArray[0]['locationID'],
            'locationName' => $responseArray[0]['locationName'],
            'locationSchedulerActive' => $responseArray[0]['locationSchedulerActive'],
            'customerID' => $responseArray[0]['customerID'],
        ];

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $requestData = [
            'locationName' => \Str::random(10),
        ];

        $apiURL = '/api/locations';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('locations', [
            'locationName' => $requestData['locationName'],
        ]);
    }

    public function testUpdateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $timezone = Timezone::inRandomOrder()->first();
        $location = Location::factory()->for($customer)->create();

        $requestData = [
            'locationName' => \Str::random(10),
            'timezoneID' => $timezone->timezoneID,
        ];

        $apiURL = '/api/locations/'.$location->locationID;
        $response = $this->withHeaders($headers)->patch($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('locations', [
            'locationID' => $location->locationID,
            'locationName' => $requestData['locationName'],
        ]);
    }

    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        User::factory()->times(rand(1, 10))->for($customer)->create([
            'userDefaultLocationID' => $location->locationID,
        ]);

        $apiURL = '/api/locations/'.$location->locationID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertSoftDeleted('locations', ['locationID' => $location->locationID]);
    }

    public function testGetUserLocationsFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create([
            'locationDefaultUser' => $user->userID,
        ]);

        $apiURL = '/api/get-user-locations';
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $location->toArray();
        $responseArray = collect($response->json('data'))->toArray();
        $responseArray = [
            'locationName' => $responseArray[0]['locationName'],
            'locationSchedulerActive' => $responseArray[0]['locationSchedulerActive'],
            'customerID' => $responseArray[0]['customerID'],
            'locationDefaultUser' => $responseArray[0]['locationDefaultUser'],
            'updated_at' => $responseArray[0]['updated_at'],
            'created_at' => $responseArray[0]['created_at'],
            'locationID' => $responseArray[0]['locationID'],
        ];

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testGetLocationsTaskProgressFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create([
            'locationDefaultUser' => $user->userID,
        ]);
        $location3 = Location::factory()->for($customer)->create();
        $location2 = Location::factory()->for($customer)->create();

        $profile = Profile::factory()->for($customer)->create();
        Security::factory()->for($profile)->for($user)->for($location)->create();

        $task1 = Task::factory()->times(rand(1, 10))->for($customer)->for($location2)->for($user)->create([
            'isDeprecated' => 0,
            'associatedTaskCompletedAt' => date('Y-m-d H:i:s'),
            'completedAt' => null,
            'dueAt' => null,
            'color' => ColorType::GREEN->value,
        ]);

        $task2 = Task::factory()->times(rand(1, 10))->for($customer)->for($location3)->for($user)->create([
            'isDeprecated' => 1,
            'associatedTaskCompletedAt' => null,
            'completedAt' => date('Y-m-d H:i:s'),
            'dueAt' => date('Y-m-d H:i:s'),
            'color' => ColorType::RED->value,
        ]);

        $task3 = Task::factory()->times(rand(1, 10))->for($customer)->for($location)->for($user)->create([
            'isDeprecated' => 0,
            'associatedTaskCompletedAt' => null,
            'completedAt' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'color' => ColorType::GREEN->value,
        ]);

        $requestData = [
            'assignedLocations' => true,
        ];

        $apiURL = '/api/locations/tasks-progress?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task3->pluck('locationID')->unique()->toArray();
        $responseArray = collect($response->json('data'))->pluck('locationID')->toArray();

        $this->assertEquals($requestArray, $responseArray);
    }
}
