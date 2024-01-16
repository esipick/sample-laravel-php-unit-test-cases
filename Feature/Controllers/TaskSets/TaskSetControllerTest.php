<?php

namespace Tests\Feature\Controllers;

use App\Enum\TaskType;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\Topic;
use App\Models\User;
use Tests\TestCase;

class TaskSetControllerTest extends TestCase
{
    public function testIndexFunctionCaseType()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task1 = Task::factory()->times(3)->for($customer)->for($location)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'type' => TaskType::RECURRING->value,
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(5)->for($customer)->for($location)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'type' => TaskType::EVENT->value,
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(3)->for($customer)->for($location)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'type' => TaskType::ASSESSMENT->value,
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'type' => TaskType::ASSESSMENT->value,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task3->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseLocationId()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location1 = Location::factory()->for($customer)->create();
        $location2 = Location::factory()->for($customer)->create();
        $location3 = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(1)->for($customer)->for($location1)->create(['isTaskSet' => 1]);
        $task2 = Task::factory()->times(6)->for($customer)->for($location2)->create(['isTaskSet' => 1]);
        $task3 = Task::factory()->times(4)->for($customer)->for($location3)->create(['isTaskSet' => 1]);

        $requestData = [
            'locationID' => $location2->locationID,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task2->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseMyTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(3)->for($customer)->for($location)->for($user)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'completedAt' => null,
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(2)->for($customer)->for($location)->for($user)->create([
            'dueAt' => null,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(4)->for($customer)->for($location)->for($user)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'myTasks' => true,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task1->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseNativeTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'templateID' => null,
            'dueAt' => null,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'templateID' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'completedAt' => null,
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'templateID' => null,
            'dueAt' => null,
            'completedAt' => null,
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'nativeTasks' => true,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task3->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOpenTaskSets()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'dueAt' => null,
            'completedAt' => null,
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'completedAt' => null,
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'openTaskSets' => true,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task2->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseDeployed()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->create([
            'templateID' => rand(1, 999),
            'dueAt' => null,
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->create([
            'templateID' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->create([
            'templateID' => null,
            'dueAt' => null,
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'deployed' => true,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task1->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseSpawnedTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'dueAt' => null,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'completedAt' => null,
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'dueAt' => null,
            'completedAt' => null,
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'spawnedTasks' => true,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task2->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseTopicId()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $topic = Topic::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($topic)->for($customer)->for($location)->for($user)->create([
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'topicID' => $topic->topicID,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task1->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseGlobalTaskSets()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'completedAt' => null,
            'isTaskItemTask' => false,
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->create([
            'dueAt' => null,
            'completedAt' => null,
            'isTaskItemTask' => false,
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->create([
            'dueAt' => null,
            'completedAt' => null,
            'isTaskItemTask' => true,
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'globalTaskSets' => true,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task2->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseGlobalDashboardTaskSets()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $profile = Profile::factory()->for($customer)->create();
        Security::factory()->for($profile)->for($user)->for($location)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->create([
            'completedAt' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->create([
            'completedAt' => null,
            'dueAt' => date('Y-m-d H:i:s'),
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->create([
            'completedAt' => date('Y-m-d H:i:s'),
            'dueAt' => null,
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'globalDashboardTaskSets' => true,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task1->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseDashboardId()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $dashboard = Dashboard::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($dashboard)->create([
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($dashboard)->create([
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->create([
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'dashboardID' => $dashboard->dashboardID,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task3->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCasePerPage()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $totalRecords = 5;
        $perPage = 2;

        Task::factory()->times($totalRecords)->for($customer)->create([
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'perPage' => $perPage,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertEquals($perPage, $response->json('data.perPage'));
        $this->assertEquals($totalRecords, $response->json('data.total'));
    }

    public function testIndexFunctionCaseSearch()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $search = 'abc';

        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->create([
            'name' => \Str::random(5).$search.\Str::random(5),
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->create([
            'name' => \Str::random(15),
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->create([
            'name' => \Str::random(15),
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'search' => $search,
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task1->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOrderByLocation()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location1 = Location::factory()->for($customer)->create(['locationName' => 'c']);
        $location2 = Location::factory()->for($customer)->create(['locationName' => 'b']);
        $location3 = Location::factory()->for($customer)->create(['locationName' => 'a']);

        $task1 = Task::factory()->times(rand(1, 3))->for($customer)->for($location3)->create([
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 3))->for($customer)->for($location1)->create([
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 3))->for($customer)->for($location2)->create([
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'orderBy' => 'desc',
            'orderByField' => 'locationID',
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $tasks = array_merge($task1->toArray(), $task2->toArray(), $task3->toArray());
        $requestArray = collect($tasks)->sortBy('locationID')->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOrderByTopic()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $topic1 = Topic::factory()->for($customer)->create(['topicTitle' => 'c']);
        $topic2 = Topic::factory()->for($customer)->create(['topicTitle' => 'b']);
        $topic3 = Topic::factory()->for($customer)->create(['topicTitle' => 'a']);

        $task1 = Task::factory()->times(rand(1, 4))->for($customer)->for($topic2)->create([
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 4))->for($customer)->for($topic3)->create([
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 4))->for($customer)->for($topic1)->create([
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'orderBy' => 'desc',
            'orderByField' => 'topicID',
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $tasks = array_merge($task2->toArray(), $task1->toArray(), $task3->toArray());
        $requestArray = collect($tasks)->sortBy('topicID')->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOrderByType()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $task1 = Task::factory()->times(rand(1, 4))->for($customer)->create([
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 4))->for($customer)->create([
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 4))->for($customer)->create([
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'orderBy' => 'desc',
            'orderByField' => 'typeID',
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $tasks = array_merge($task3->toArray(), $task2->toArray(), $task1->toArray());
        $requestArray = collect($tasks)->sortBy('topicID')->pluck('typeID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('typeID')->toArray();

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOrderByDefault()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $task1 = Task::factory()->times(rand(1, 4))->for($customer)->create([
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->times(rand(1, 4))->for($customer)->create([
            'isTaskSet' => 1,
        ]);

        $task3 = Task::factory()->times(rand(1, 4))->for($customer)->create([
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'orderBy' => 'asc',
            'orderByField' => 'created_at',
        ];

        $apiURL = '/api/task-sets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $tasks = array_merge($task1->toArray(), $task3->toArray(), $task2->toArray());
        $requestArray = collect($tasks)->sortBy('created_at')->pluck('typeID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('typeID')->toArray();

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testTaskTypesFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $apiURL = '/api/task-sets/types';
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertEquals(TaskType::RECURRING->value, collect($response->json('data'))->where('name', 'Recurring')->pluck('value')->first());
        $this->assertEquals(TaskType::EVENT->value, collect($response->json('data'))->where('name', 'Event')->pluck('value')->first());
        $this->assertEquals(TaskType::ASSESSMENT->value, collect($response->json('data'))->where('name', 'Assessment')->pluck('value')->first());
    }

    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $taskName = \Str::random(10);
        $requestData = [
            'name' => $taskName,
            'locationID' => $location->locationID,
            'customerID' => $customer->customerID,
        ];

        $apiURL = '/api/task-sets';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks', [
            'name' => $taskName,
            'locationID' => $location->locationID,
            'customerID' => $customer->customerID,
        ]);
    }

    public function testShowFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $taskName = \Str::random(10);
        $task = Task::factory()->for($customer)->for($location)->create([
            'name' => $taskName,
            'locationID' => $location->locationID,
            'customerID' => $customer->customerID,
        ]);

        $apiURL = '/api/task-sets/'.$task->taskID;
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $data = $response->json('data');

        $this->assertEquals(
            [
                'name' => $taskName,
                'locationID' => $location->locationID,
                'customerID' => $customer->customerID,
            ],
            [
                'name' => $data['name'],
                'locationID' => $data['locationID'],
                'customerID' => $data['customerID'],
            ]
        );
    }

    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => null]);

        Task::factory()->for($customer)->for($location)->create([
            'taskSetTemplateID' => $task->taskID,
            'isLocalAddedTask' => true,
        ]);

        $apiURL = '/api/task-sets/'.$task->taskID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertSoftDeleted('tasks', ['taskID' => $task->taskID]);
    }
}
