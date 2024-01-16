<?php

namespace Tests\Feature\Controllers;

use App\Enum\TaskType;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Topic;
use App\Models\User;
use Tests\TestCase;

class EventsControllerTest extends TestCase
{
    public function testIndexFunctionCaseLocationId()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'taskSetTemplateID' => null,
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'taskSetTemplateID' => null,
            'dueAt' => null,
            'type' => TaskType::RECURRING->value,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'taskSetTemplateID' => null,
            'dueAt' => null,
            'type' => TaskType::ASSESSMENT->value,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
        ];

        $apiURL = '/api/events?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task1->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseTopicId()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $topic = Topic::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($topic)->for($customer)->for($location)->for($user)->create([
            'taskSetTemplateID' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'type' => TaskType::EVENT->value,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($topic)->for($customer)->for($location)->for($user)->create([
            'taskSetTemplateID' => null,
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($topic)->for($customer)->for($location)->for($user)->create([
            'taskSetTemplateID' => rand(1, 10),
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'topicID' => $topic->topicID,
        ];

        $apiURL = '/api/events?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task2->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCasePerPage()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        Task::factory()->times(rand(10, 15))->for($customer)->for($location)->for($user)->create([
            'taskSetTemplateID' => null,
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
        ]);

        $perPage = rand(1, 10);
        $requestData = [
            'locationID' => $location->locationID,
            'perPage' => $perPage,
        ];

        $apiURL = '/api/events?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $perPage;
        $responseArray = $response->json('data.perPage');

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseSearch()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $search = 'abcdefghij';
        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'name' => $search.\Str::random(5),
            'taskSetTemplateID' => rand(1, 10),
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'name' => \Str::random(5).$search,
            'taskSetTemplateID' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'type' => TaskType::EVENT->value,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'name' => $search.\Str::random(5),
            'taskSetTemplateID' => null,
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'search' => $search,
        ];

        $apiURL = '/api/events?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task3->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOrderBy()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'taskSetTemplateID' => rand(1, 10),
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
        ]);

        $task2 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'taskSetTemplateID' => null,
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
        ]);

        $task3 = Task::factory()->times(rand(1, 5))->for($customer)->for($location)->for($user)->create([
            'taskSetTemplateID' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'type' => TaskType::EVENT->value,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'orderBy' => 'ASC',
            'orderByField' => 'name',

        ];

        $apiURL = '/api/events?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = collect($task2)->sortBy('name')->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $requestData = [
            'name' => \Str::random(10),
            'locationID' => $location->locationID,
            'isLocalAddedTask' => true,
        ];

        $apiURL = '/api/events';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks', [
            'name' => $requestData['name'],
            'locationID' => $requestData['locationID'],
            'isLocalAddedTask' => $requestData['isLocalAddedTask'],
        ]);
    }

    public function testShowFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $search = 'important task';
        $task = Task::factory()->for($customer)->for($location)->for($user)->create([
            'name' => $search,
            'taskSetTemplateID' => null,
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
        ]);

        $apiURL = '/api/events/'.$task->taskID;
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = [
            'taskID' => $task->taskID,
            'name' => $search,
            'taskSetTemplateID' => null,
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
        ];

        $response = $response->json('data');
        $responseArray = [
            'taskID' => $response['taskID'],
            'name' => $response['name'],
            'taskSetTemplateID' => $response['taskSetTemplateID'],
            'dueAt' => $response['dueAt'],
            'type' => $response['type'],
        ];

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testUpdateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $task1 = Task::factory()->for($customer)->for($location)->for($user)->create([
            'name' => \Str::random(25),
            'taskSetTemplateID' => null,
            'dueAt' => null,
            'type' => TaskType::EVENT->value,
            'isTaskSet' => 1,
        ]);

        $task2 = Task::factory()->for($customer)->for($location)->for($user)->create([
            'name' => 'create task',
            'taskSetTemplateID' => $task1->taskID,
            'dueAt' => null,
            'type' => TaskType::ASSESSMENT->value,
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'name' => 'update task',
            'typeID' => TaskType::EVENT->value,
            'turnGreen' => '44',
            'turnYellow' => '33',
            'turnRed' => '23',
        ];

        $apiURL = '/api/events/'.$task2->taskID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks', [
            'taskID' => $task2->taskID,
            'name' => 'update task',
            'typeID' => TaskType::EVENT->value,
            'turnGreen' => '44',
            'turnYellow' => '33',
            'turnRed' => '23',
        ]);
    }

    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => null]);

        $apiURL = '/api/events/'.$task->taskID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertSoftDeleted('tasks', ['taskID' => $task->taskID]);
    }
}
