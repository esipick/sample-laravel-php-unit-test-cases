<?php

namespace Feature\Controllers\CompletedTasks;

use App\Enum\TaskType;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CompletedTaskControllerTest extends TestCase
{
    public function testCompletedTaskGetFilters()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        $response = $this->withHeaders($headers)
            ->get('/api/completed-tasks/filter?locationID='.$location->locationID);

        $response->assertStatus(200);
    }

    public function testGetCompletedTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        Task::factory()->for($customer)->for($location)->for($user2)->create(['name' => 'taskB', 'completedAt' => now()]);
        Task::factory()->for($customer)->for($location)->for($user)->create(['name' => 'taskB', 'completedAt' => now()]);

        $response = $this->withHeaders($headers)
            ->get('/api/completed-tasks?locationID='.$location->locationID.'&page=1');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    public function testCompletedTaskIndexWithCustomParameters()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $locationB = Location::factory()->for($customer)->create(['locationName' => 'locationB']);

        $locationATask1 = Task::factory()->for($customer)->for($locationA)->create(['name' => 'locationATask1', 'completedAt' => now()]);
        $locationBTask1 = Task::factory()->for($customer)->for($locationB)->create(['name' => 'locationBTask1', 'completedAt' => now()]);
        $locationATask2 = Task::factory()->for($customer)->for($locationA)->create(['name' => 'locationATask2', 'completedAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/completed-tasks?orderBy=asc&orderByField=completedAt');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $taskNames = array_column($data, 'name');

        $this->assertEquals([$locationATask1->name, $locationBTask1->name, $locationATask2->name], $taskNames);
    }

    public function testCompletedTaskIndexFiltersByStartDateAndEndDate()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $startDate = now()->subDays(5);
        $endDate = now()->subDays(2);

        $oldTask = Task::factory()->for($customer)->create(['created_at' => now()->subDays(7)]);
        Task::factory()->for($customer)->create(['created_at' => $startDate, 'completedAt' => now()]);
        Task::factory()->for($customer)->create(['created_at' => $endDate, 'completedAt' => now()]);
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');
        $response = $this->withHeaders($headers)->get("/api/completed-tasks?startDate=$startDate&endDate=$endDate");

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
        $data = $response->json('data.data');
        $taskNames = array_column($data, 'name');
        $this->assertNotContains($oldTask->name, $taskNames);
    }

    public function testCompletedTaskIndexPaginatesResults()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        Task::factory()->for($customer)->count(15)->create();

        $response = $this->withHeaders($headers)->get('/api/completed-tasks?perPage=5');

        $response->assertStatus(200);
    }

    public function testCompletedTaskIndexSearchesTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $searchTerm = 'important task';

        $matchingTask = Task::factory()->for($customer)->create(['name' => $searchTerm, 'completedAt' => now()]);
        $nonMatchingTask = Task::factory()->for($customer)->create(['name' => 'routine task', 'completedAt' => now()]);

        $response = $this->withHeaders($headers)->get("/api/completed-tasks?search=$searchTerm");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertSee($matchingTask->name);
        $response->assertDontSee($nonMatchingTask->name);
    }

    public function testCompletedTaskIndexSortsTasksByField()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $locationB = Location::factory()->for($customer)->create(['locationName' => 'locationB']);
        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);
        $topicB = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle B']);

        $task1 = Task::factory()->for($customer)->for($locationA)->for($topicA)->create(['completedAt' => now()]);
        $task2 = Task::factory()->for($customer)->for($locationB)->for($topicB)->create(['completedAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/completed-tasks?orderBy=asc&orderByField=topicID');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$task1->name, $task2->name]);
    }

    public function testCompletedTaskIndexSortsTasksByFieldTypeID()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $headers = $this->authenticateUser($user);

        $task1 = Task::factory()->for($customer)->create(['type' => TaskType::RECURRING->value, 'completedAt' => now()]);
        $task2 = Task::factory()->for($customer)->create(['type' => TaskType::ASSESSMENT->value, 'completedAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/completed-tasks?orderBy=asc&orderByField=typeID');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$task2->name, $task1->name]);
    }

    public function testCompletedTaskIndexSortsTasksByFieldCompletedAt()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $headers = $this->authenticateUser($user);
        $twoDaysAgo = now()->subDays(2);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $task1 = Task::factory()->for($customer)->for($locationA)->create(['name' => 'taskB', 'dueAt' => $twoDaysAgo, 'completedAt' => now()]);
        $task2 = Task::factory()->for($customer)->for($locationA)->create(['name' => 'taskA', 'dueAt' => now(), 'completedAt' => Carbon::yesterday()]);

        $response = $this->withHeaders($headers)->get('/api/completed-tasks?hasCompletedTasksFromTaskSets=1&orderBy=asc&orderByField=completedAt');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
        $data = $response->json('data.data');
        $taskNames = array_column($data, 'name');
        $this->assertEquals([$task2->name, $task1->name], $taskNames);

    }

    public function testCompletedTaskIndexAssignedToTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $user1 = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $matchingTask = Task::factory()->for($user)->for($customer)->create(['completedAt' => now()]);
        $nonMatchingTask = Task::factory()->for($user1)->for($customer)->create(['name' => 'routine task', 'completedAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/completed-tasks?assignedTo='.$user->userID);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertSee($matchingTask->name);
        $response->assertDontSee($nonMatchingTask->name);
    }

    public function testCompletedTaskIndexCompletedByTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $user1 = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $matchingTask = Task::factory()->for($user, 'completedByUser')->for($customer)->create(['completedAt' => now()]);
        $nonMatchingTask = Task::factory()->for($user1)->for($customer)->create(['name' => 'routine task', 'completedAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/completed-tasks?completedBy='.$user->userID);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertSee($matchingTask->name);
        $response->assertDontSee($nonMatchingTask->name);
    }

    public function testCompletedTaskIndexDueDateStartTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $user1 = User::factory()->for($customer)->create();
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $headers = $this->authenticateUser($user);
        $yesterday = \Carbon\Carbon::yesterday()->addDay(4);
        $matchingTask = Task::factory()->for($locationA)->for($user, 'completedByUser')->for($customer)->create(['dueAt' => $yesterday, 'completedAt' => now()]);
        $nonMatchingTask = Task::factory()->for($locationA)->for($user1)->for($customer)->create(['dueAt' => now(), 'name' => 'routine task', 'completedAt' => now()]);
        $dueDateStart = $yesterday->format('Y-m-d');
        $response = $this->withHeaders($headers)->get('/api/completed-tasks?dueDateStart='.$dueDateStart);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertSee($matchingTask->name);
        $response->assertDontSee($nonMatchingTask->name);
    }

    public function testCompletedTaskIndexCompletedDateStartTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $user1 = User::factory()->for($customer)->create();
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $headers = $this->authenticateUser($user);
        $yesterday = \Carbon\Carbon::yesterday()->addDay(4);
        $date1 = \Carbon\Carbon::yesterday()->addDay(1);
        Task::factory()->for($locationA)->for($user, 'completedByUser')->for($customer)->create(['completedAt' => $yesterday, 'completedAt' => now()]);
        Task::factory()->for($locationA)->for($user1)->for($customer)->create(['completedAt' => now(), 'name' => 'routine task', 'completedAt' => now()]);
        $completedDateStart = $yesterday->format('Y-m-d');
        $date1 = $date1->format('Y-m-d');
        $response = $this->withHeaders($headers)->get('/api/completed-tasks?completedDateStart='.$date1.'&completedDateEnd='.$completedDateStart);

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    public function testCompletedTaskIndexSortsCompletedByField()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $locationB = Location::factory()->for($customer)->create(['locationName' => 'locationB']);
        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);
        $topicB = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle B']);

        $task1 = Task::factory()->for($customer)->for($locationA)->for($topicA)->create(['completedAt' => now()]);
        $task2 = Task::factory()->for($customer)->for($locationB)->for($topicB)->create(['completedAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/completed-tasks?orderBy=asc&orderByField=completedBy');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$task1->name, $task2->name]);
    }

    public function testStoreTaskCompletion()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);

        $task = Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create();

        $response = $this->withHeaders($headers)
            ->post('/api/completed-tasks', ['taskID' => $task->taskID, 'userCompleted' => $user->userID]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'message']);

        $completedTask = Task::find($task->taskID);
        $this->assertNotNull($completedTask->completedAt);
        $this->assertEquals($task->userID, $completedTask->userCompleted);
    }

    public function testStoreTaskAlreadyCompleted()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);

        $task = Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create(['completedAt' => now()]);

        $response = $this->withHeaders($headers)
            ->post('/api/completed-tasks', ['taskID' => $task->taskID, 'userCompleted' => $user->userID]);

        $response->assertStatus(500);
    }

    public function testStoreTaskAlreadyInvalidTaskCompleted()
    {
        $customer = Customer::factory()->create();
        $customer1 = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);
        $taskSet = Task::factory()->for($user)->for($customer1)->for($locationA)->for($topicA)->create(['isTaskSet' => true]);
        $task = Task::factory()->for($user)->for($customer1)->for($locationA)->for($taskSet, 'parent')->for($topicA)->create();

        $response = $this->withHeaders($headers)
            ->post('/api/completed-tasks', ['taskID' => $task->taskID]);

        $response->assertStatus(500);
    }

    public function testStoreTaskAlreadyValidTaskSetCompleted()
    {
        $customer = Customer::factory()->create();

        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);
        $taskSet = Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create(['isTaskSet' => true]);
        Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create(['taskSetTemplateID' => $taskSet->taskID]);
        $task = Task::factory()->for($user)->for($customer)->for($locationA)->for($taskSet, 'parent')->for($topicA)->create();

        $response = $this->withHeaders($headers)
            ->post('/api/completed-tasks', ['taskID' => $taskSet->taskID]);

        $response->assertStatus(200);
    }

    public function testStoreTaskAlreadyValidTaskCompleted()
    {
        $customer = Customer::factory()->create();

        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);
        $task = Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create();
        $task1 = Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create(['taskSetTemplateID' => $task->taskID]);

        $response = $this->withHeaders($headers)
            ->post('/api/completed-tasks', ['taskID' => $task1->taskID]);

        $response->assertStatus(200);
    }

    public function testStoreTaskAlreadyValidTaskRedColorCompleted()
    {
        $customer = Customer::factory()->create();

        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);
        $task = Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create();
        $task1 = Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create(['taskSetTemplateID' => $task->taskID, 'color' => 3]);

        $response = $this->withHeaders($headers)
            ->post('/api/completed-tasks', ['taskID' => $task1->taskID]);

        $response->assertStatus(200);
    }

    public function testStoreSetNotifyOnTaskSetCompletionCompleted()
    {
        $customer = Customer::factory()->create();

        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);
        $task = Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create(['isTaskSet' => false, 'notifyOnTaskSetCompletion' => 1, 'completedAt' => null]);
        $taskSet = Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create(['isTaskSet' => false, 'notifyOnTaskSetCompletion' => 1, 'taskSetTemplateID' => $task->taskID, 'completedAt' => null]);
        Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create(['isTaskSet' => false, 'taskSetTemplateID' => $task->taskID, 'notifyOnTaskSetCompletion' => 1, 'completedAt' => now()]);
        Task::factory()->for($user)->for($customer)->for($locationA)->for($topicA)->create(['isTaskSet' => false, 'taskSetTemplateID' => $task->taskID, 'notifyOnTaskSetCompletion' => 1, 'completedAt' => now()]);
        $task = Task::factory()->for($user)->for($customer)->for($locationA)->for($taskSet, 'parent')->for($topicA)->create();

        $response = $this->withHeaders($headers)
            ->post('/api/completed-tasks', ['taskID' => $taskSet->taskID]);

        $response->assertStatus(200);
    }
}
