<?php

namespace Feature\Controllers\CompletedTaskSets;

use App\Enum\TaskType;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class CompletedTaskSetControllerTest extends TestCase
{
    public function testGetFiltersFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $profileAssignedTo = Profile::factory()->for($customer)->create();
        $userAssignedTo = User::factory()->for($customer)->create();

        Task::factory()->times(rand(1, 5))->for($userAssignedTo)->for($profileAssignedTo)->for($customer)->for($location)->create([
            'userCompleted' => $user->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
        ];

        $apiURL = '/api/completed-taskSets/filters?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $responseArray = $response->json('data');

        $this->assertEquals($user->userID, $responseArray['completedBy']['users'][0]['userID']);
        $this->assertEquals($userAssignedTo->userID, $responseArray['assignedTo']['users'][0]['userID']);
        $this->assertEquals($profileAssignedTo->profileID, $responseArray['assignedTo']['profiles'][0]['profileID']);
    }

    public function testIndexFunctionPerPage()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $perPage = 3;
        $requestData = [
            'locationID' => $location->locationID,
            'perPage' => $perPage,
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $responseArray = collect($response->json('data'))->toArray();
        $totalCount = ($task1->count() + $task2->count() + $task3->count());

        $this->assertEquals($perPage, $responseArray['perPage']);
        $this->assertEquals($totalCount, $responseArray['total']);
    }

    public function testIndexFunctionCaseSearch()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $searchText = 'abcdefghij';
        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'name' => \Str::random(5).$searchText.\Str::random(5),
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'search' => $searchText,
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task1->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseCompletedBy()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $searchText = 'abcdefghij';
        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $user3->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'completedBy' => $user3->userID,
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task3->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseAssignedTo()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'assignedTo' => $user2->userID,
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task2->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseDueDateStart()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'dueAt' => null,
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'dueAt' => date('Y-m-d H:i:s'),
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 2,
            'dueAt' => date('Y-m-d H:i:s'),
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'dueDateStart' => date('Y-m-d'),
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task2->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseDueDateEnd()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'dueAt' => date('Y-m-d H:i:s'),
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => null,
            'isTaskSet' => 1,
            'dueAt' => date('Y-m-d H:i:s'),
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'dueAt' => null,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'dueDateEnd' => date('Y-m-d'),
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task1->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseCompletedDateStart()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 2,
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => null,
            'isTaskSet' => 1,
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'completedDateStart' => date('Y-m-d'),
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task3->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseCompletedDateEnd()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => null,
            'isTaskSet' => 1,
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 2,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'completedDateEnd' => date('Y-m-d'),
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $task1->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOrderByCompletedBy()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'orderBy' => 'ASC',
            'orderByField' => 'completedBy',
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = collect(array_merge($task1->toArray(), $task2->toArray(), $task3->toArray()))->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        // $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOrderByTypeId()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'type' => TaskType::EVENT->value,
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'type' => TaskType::ASSESSMENT->value,
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'type' => TaskType::RECURRING->value,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'orderBy' => 'desc',
            'orderByField' => 'typeID',
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = collect(array_merge($task1->toArray(), $task2->toArray(), $task3->toArray()))->sortBy('type')->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        //        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOrderByDefault()
    {
        $customer = Customer::factory()->create();
        $userAuth = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($userAuth);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $task1 = Task::factory()->times(rand(1, 5))->for($user)->for($profile)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'type' => TaskType::EVENT->value,
        ]);

        $profile2 = Profile::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $task2 = Task::factory()->times(rand(1, 5))->for($user2)->for($profile2)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'type' => TaskType::ASSESSMENT->value,
        ]);

        $profile3 = Profile::factory()->for($customer)->create();
        $user3 = User::factory()->for($customer)->create();
        $task3 = Task::factory()->times(rand(1, 5))->for($user3)->for($profile3)->for($customer)->for($location)->create([
            'userCompleted' => $userAuth->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
            'type' => TaskType::RECURRING->value,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'orderBy' => 'desc',
            'orderByField' => 'taskID',
        ];

        $apiURL = '/api/completed-taskSets?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = collect(array_merge($task1->toArray(), $task2->toArray(), $task3->toArray()))->pluck('taskID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('taskID')->toArray();

        rsort($requestArray);

        // $this->assertEquals($requestArray, $responseArray);
    }
}
