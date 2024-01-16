<?php

namespace Feature\Controllers\Tasks;

use App\Enum\TaskType;
use App\Jobs\DeployTaskJob;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategory;
use App\Models\DashboardTaskSetCategory;
use App\Models\DashboardTaskSetsItem;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\Topic;
use App\Models\User;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    public function testGetTasksWithValidData()
    {
        // Mock authentication
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $headers = $this->authenticateUser($user);

        $requestData = [
            'locationID' => 1,
            'openTasks' => false,
            'deployed' => true,
        ];

        $response = $this->withHeaders($headers)->get('/api/tasks', $requestData);
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testGetTasksWithInvalidData()
    {
        // Mock authentication
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $headers = $this->authenticateUser($user);

        $invalidData = ['locationID' => 56];

        $response = $this->withHeaders($headers)->post('/api/tasks', $invalidData);
        $response->assertStatus(422);

        // Assert that the response contains validation errors
        $response->assertJsonValidationErrors([
            'locationID',
        ]);
    }

    public function testIndexReturnsTasksForAuthenticatedUser()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $this->actingAs($user);
        Task::factory()->for($customer)->count(5)->create();

        $headers = $this->authenticateUser($user);
        $response = $this->withHeaders($headers)->get('/api/tasks');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    public function testGetTasksUnauthenticated()
    {
        $response = $this->postJson('/api/tasks');

        $response->assertStatus(401);
    }

    public function testGetTasksWithSearch()
    {
        // Mock authentication
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $headers = $this->authenticateUser($user);
        $requestData = [
            'search' => 'example-search-term',
        ];

        $response = $this->withHeaders($headers)->getJson('/api/tasks', $requestData);

        // Assert that the response is successful and in the expected format
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testIndexFiltersByType()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $headers = $this->authenticateUser($user);

        Task::factory()->for($customer)->count(3)->create(['type' => TaskType::RECURRING->value]);
        Task::factory()->for($customer)->count(2)->create(['type' => TaskType::ASSESSMENT->value]);

        $response = $this->withHeaders($headers)->get('/api/tasks?type=1');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data.data');
    }

    public function testIndexSortsTasksByLocation()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $locationB = Location::factory()->for($customer)->create(['locationName' => 'locationB']);

        $locationATask1 = Task::factory()->for($customer)->for($locationA)->create(['name' => 'locationATask1']);
        $locationBTask1 = Task::factory()->for($customer)->for($locationB)->create(['name' => 'locationBTask1']);
        $locationATask2 = Task::factory()->for($customer)->for($locationA)->create(['name' => 'locationATask2']);

        $response = $this->withHeaders($headers)->get('/api/tasks?orderBy=asc&orderByField=locationID');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $taskNames = array_column($data, 'name');

        $this->assertEquals([$locationATask1->name, $locationATask2->name, $locationBTask1->name], $taskNames);
    }

    public function testIndexFiltersByStartDateAndEndDate()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $startDate = now()->subDays(5);
        $endDate = now()->subDays(2);

        $oldTask = Task::factory()->for($customer)->create(['created_at' => now()->subDays(7)]);
        Task::factory()->for($customer)->create(['created_at' => $startDate]);
        Task::factory()->for($customer)->create(['created_at' => $endDate]);
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');
        $response = $this->withHeaders($headers)->get("/api/tasks?startDate=$startDate&endDate=$endDate");

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
        $data = $response->json('data.data');
        $taskNames = array_column($data, 'name');
        $this->assertNotContains($oldTask->name, $taskNames);
    }

    public function testIndexPaginatesResults()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        Task::factory()->for($customer)->count(15)->create();

        $response = $this->withHeaders($headers)->get('/api/tasks?perPage=5');

        $response->assertStatus(200);
    }

    public function testIndexSearchesTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $searchTerm = 'important task';

        $matchingTask = Task::factory()->for($customer)->create(['name' => $searchTerm]);
        $nonMatchingTask = Task::factory()->for($customer)->create(['name' => 'routine task']);

        $response = $this->withHeaders($headers)->get("/api/tasks?search=$searchTerm");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
        $response->assertSee($matchingTask->name);
        $response->assertDontSee($nonMatchingTask->name);
    }

    public function testIndexSortsTasksByField()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $locationB = Location::factory()->for($customer)->create(['locationName' => 'locationB']);
        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);
        $topicB = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle B']);

        $task1 = Task::factory()->for($customer)->for($locationA)->for($topicA)->create();
        $task2 = Task::factory()->for($customer)->for($locationB)->for($topicB)->create();

        $response = $this->withHeaders($headers)->get('/api/tasks?orderBy=asc&orderByField=topicID');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$task1->name, $task2->name]);
    }

    public function testIndexSortsTasksByFieldTypeID()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $headers = $this->authenticateUser($user);

        $task1 = Task::factory()->for($customer)->create(['type' => TaskType::RECURRING->value]);
        $task2 = Task::factory()->for($customer)->create(['type' => TaskType::ASSESSMENT->value]);

        $response = $this->withHeaders($headers)->get('/api/tasks?orderBy=asc&orderByField=typeID');

        $response->assertStatus(200);
        $response->assertSeeInOrder([$task2->name, $task1->name]);
    }

    public function testIndexSortsTasksByFieldDueAt()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $headers = $this->authenticateUser($user);
        $twoDaysAgo = now()->subDays(2);
        $locationA = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $task1 = Task::factory()->for($customer)->for($locationA)->create(['name' => 'taskB', 'dueAt' => $twoDaysAgo]);
        $task2 = Task::factory()->for($customer)->for($locationA)->create(['name' => 'taskA', 'dueAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/tasks?orderBy=desc&orderByField=dueAt');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
        $data = $response->json('data.data');
        $taskNames = array_column($data, 'name');
        $this->assertEquals([$task2->name, $task1->name], $taskNames);

    }

    public function testMyTasksFilter()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        Task::factory()->for($customer)->for($location)->for($user)->create(['name' => 'taskB', 'dueAt' => now()]);
        Task::factory()->for($customer)->for($location)->for($user2)->create(['name' => 'taskB', 'dueAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/tasks?myTasks=1');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testNativeTasksFilter()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        Task::factory()->for($customer)->for($location)->for($user)->create(['isTaskItemTask' => false]);
        Task::factory()->for($customer)->for($location)->for($user2)->create(['name' => 'taskB', 'dueAt' => now()]);
        $response = $this->withHeaders($headers)->get('/api/tasks?nativeTasks=1');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testOpenTasksFilter()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        Task::factory()->for($customer)->for($location)->for($user2)->create(['name' => 'taskB', 'dueAt' => now()]);
        Task::factory()->for($customer)->for($location)->for($user2)->create(['name' => 'taskB', 'dueAt' => null]);

        $response = $this->withHeaders($headers)->get('/api/tasks?openTasks=1');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testOpenTasksFilterHasLocationDefaultUser()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['isLocationDefaultUser' => false, 'userType' => null]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        $filteredOutTasks = Task::factory()->for($customer)->for($location)->for($user)->count(5)->create(['dueAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/tasks?openTasks=1');
        $response->assertStatus(200);
        $data = $response->json('data.data');
        $taskNames = array_column($data, 'name');

        foreach ($filteredOutTasks as $task) {
            $this->assertContains($task->name, $taskNames);
        }
    }

    public function testOpenTasksFilterLocationID()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        Task::factory()->for($customer)->for($location)->for($user)->count(5)->create();

        $response = $this->withHeaders($headers)->get('/api/tasks?locationID='.$location->locationID);
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data.data');
    }

    public function testOpenTasksFilterDeployed()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $globalTask = Task::factory()->for($customer)->create();
        Task::factory()->for($customer)->for($location)->for($user)->count(1)->create(['templateID' => $globalTask->taskID, 'dueAt' => null]);

        $response = $this->withHeaders($headers)->get('/api/tasks?deployed=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testOpenTasksFilterSpawnedTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        Task::factory()->for($customer)->for($location)->for($user)->count(1)->create(['dueAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/tasks?spawnedTasks=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testOpenTasksFilterTopicID()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $topic = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);
        Task::factory()->for($customer)->for($location)->for($topic)->count(1)->create(['dueAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/tasks?topicID='.$topic->topicID);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testOpenTasksFilterGlobalTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        Task::factory()->for($customer)->count(1)->create(['isTaskItemTask' => false]);

        $response = $this->withHeaders($headers)->get('/api/tasks?globalTasks=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testOpenTasksFilterGlobalDashboardTasks()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        Task::factory()->for($customer)->for($location)->count(1)->create(['isTaskItemTask' => false, 'dueAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/tasks?globalDashboardTasks=1');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testStoreTaskSuccessfully()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        Task::factory()->for($customer)->count(1)->create();

        $validTaskData = [
            'name' => 'New Task',
        ];

        $response = $this->withHeaders($headers)->post('/api/tasks', $validTaskData);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
        $this->assertDatabaseHas('tasks', ['name' => 'New Task']);
    }

    public function testUpdateTaskSuccessfully()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $subTask = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();
        $task = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create(['isTaskSet' => true, 'shortName' => 'shortName1', 'typeID' => 3]);
        $subTask->taskSetTemplateID = $task->taskID;
        $subTask->save();

        $topicA = Topic::factory()->for($customer)->create(['topicTitle' => 'topicTitle A']);
        $validUpdateData = [
            'name' => 'Updated Task',
            'unassigned' => 1,
            'profileID' => 'NULL',
            'userID' => 'NULL',
            'shortName' => 'shortName',
            'topicID' => $topicA->topicID,
            'turnGreen' => 1,
            'turnYellow' => 1,
            'turnRed' => 1,
        ];

        $response = $this->withHeaders($headers)->put("/api/tasks/{$task->taskID}", $validUpdateData);
        $response->assertStatus(200)->assertJson(['status' => 'Success']);
        $this->assertDatabaseHas('tasks', ['taskID' => $task->taskID, 'name' => 'Updated Task']);
    }

    public function testUpdateTaskFailsValidation()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $task = Task::factory()->for($customer)->for($location)->create();

        $invalidUpdateData = [
            'topicID' => 144,
        ];
        $response = $this->withHeaders($headers)->put("/api/tasks/{$task->taskID}", $invalidUpdateData);

        $response->assertStatus(422);
    }

    public function testUpdateNonExistentTask()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $invalidTaskId = 999; // A non-existent task ID

        $validUpdateData = [
            'name' => 'Updated Task',
        ];

        $response = $this->withHeaders($headers)->put("/tasks/{$invalidTaskId}", $validUpdateData);

        $response->assertStatus(404);
    }

    public function testUpdateSubTaskSuccessfully()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $subTask = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();

        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $task = Task::factory()->for($customer)->for($dashboard)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($dashboardCategory)->create();

        $subTask->taskSetTemplateID = $task->taskID;
        $subTask->save();

        $validUpdateData = [
            'name' => 'Updated Sub Task',
            'taskSetTemplateID' => $task->taskID,
            'profileID' => 'NULL',
        ];

        $response = $this->withHeaders($headers)->put("/api/tasks/{$subTask->taskID}", $validUpdateData);
        $response->assertStatus(200)->assertJson(['status' => 'Success']);
        $this->assertDatabaseHas('tasks', ['taskID' => $subTask->taskID, 'name' => 'Updated Sub Task']);
    }

    public function testUpdateSubTaskRemoveTaskSetTemplateIDSuccessfully()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $subTask = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();

        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $task = Task::factory()->for($customer)->for($dashboard)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($dashboardCategory)->create();

        $subTask->taskSetTemplateID = $task->taskID;
        $subTask->save();

        $validUpdateData = [
            'name' => 'Updated Sub Task',
            'taskSetTemplateID' => null,
            'profileID' => 'NULL',
        ];

        $response = $this->withHeaders($headers)->put("/api/tasks/{$subTask->taskID}", $validUpdateData);
        $response->assertStatus(200)->assertJson(['status' => 'Success']);
        $this->assertDatabaseHas('tasks', ['taskID' => $subTask->taskID, 'name' => 'Updated Sub Task']);
    }

    public function testUpdateTaskSetSuccessfully()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $subTask = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();
        $subTask2 = Task::factory()->for($customer)->for($location)->for($dashboard)->create();

        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $task = Task::factory()->for($customer)->for($dashboard)->for($profile)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($dashboardCategory)->create();
        $profile2 = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $subTask->taskSetTemplateID = $task->taskID;
        $subTask2->taskSetTemplateID = $task->taskID;
        $subTask->save();
        $subTask2->save();

        $validUpdateData = [
            'profileID' => $profile2->profileID,
            'assignToAllTaskChildren' => true,
            'typeID' => 2,
            'type' => TaskType::ASSESSMENT->value,
        ];

        $response = $this->withHeaders($headers)->put("/api/tasks/{$task->taskID}", $validUpdateData);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
        $this->assertDatabaseHas('tasks', ['taskID' => $subTask->taskID, 'type' => TaskType::ASSESSMENT->value]);
    }

    public function testUpdateTaskSetShortNameExist()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        Task::factory()->for($customer)->for($dashboard)->for($profile)->create(['isTaskSet' => 1, 'shortName' => 'shortName']);
        $task = Task::factory()->for($customer)->for($dashboard)->for($profile)->create(['isTaskSet' => 1]);

        $validUpdateData = [
            'shortName' => 'shortName',
        ];

        $response = $this->withHeaders($headers)->put("/api/tasks/{$task->taskID}", $validUpdateData);

        $response->assertStatus(500);
    }

    public function testUpdateAssignToAllUnassignedTaskChildrenSuccessfully()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $user1 = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true, 'notifyEmailAssignmentGroup' => true]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $subTask = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();
        $subTask2 = Task::factory()->for($customer)->for($location)->for($dashboard)->create();

        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();
        $task = Task::factory()->for($customer)->for($dashboard)->for($profile)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($task)->for($user)->for($dashboard)->create();
        DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem, 'dashboardTasksetsItem')->for($dashboardCategory)->create();
        $profile2 = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user1)->for($location)->for($profile2)->create();

        $subTask->taskSetTemplateID = $task->taskID;
        $subTask2->taskSetTemplateID = $task->taskID;
        $subTask->save();
        $subTask2->save();

        $validUpdateData = [
            'profileID' => $profile2->profileID,
            'userID' => $user->userID,
            'assignToAllUnassignedTaskChildren' => true,
            'typeID' => 2,
            'type' => TaskType::ASSESSMENT->value,
        ];

        $response = $this->withHeaders($headers)->put("/api/tasks/{$task->taskID}", $validUpdateData);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
        $this->assertDatabaseHas('tasks', ['taskID' => $subTask->taskID, 'type' => TaskType::ASSESSMENT->value]);
    }

    public function testShowTaskSuccessfully()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $task = Task::factory()->for($customer)->for($location)->for($user)->create(['dueAt' => now()]);
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->get("/api/tasks/{$task->taskID}");

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
    }

    public function testShowNonExistentTask()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);

        $invalidTaskId = 999;

        $response = $this->withHeaders($headers)->get("/api/tasks/{$invalidTaskId}");

        $response->assertStatus(404);
    }

    public function testDestroyTaskSetSuccessfully()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $task = Task::factory()->for($customer)->for($location)->for($user)->create(['dueAt' => now(), 'isTaskSet' => true]);
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->delete("/api/tasks/{$task->taskID}");

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
        $this->assertSoftDeleted('tasks', ['taskID' => $task->taskID]);
    }

    public function testDestroyTaskSuccessfully()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $task = Task::factory()->for($customer)->for($location)->for($user)->create(['dueAt' => now()]);
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->delete("/api/tasks/{$task->taskID}");

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
        $this->assertSoftDeleted('tasks', ['taskID' => $task->taskID]);
    }

    public function testDestroyNonExistentTask()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $task = Task::factory()->for($customer)->for($location)->for($user)->create(['dueAt' => now()]);
        $headers = $this->authenticateUser($user);

        $invalidTaskId = 999;

        $response = $this->withHeaders($headers)->delete("/api/tasks/{$invalidTaskId}");

        $response->assertStatus(404);
    }

    public function testResetTaskSuccessfully()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $task = Task::factory()->for($customer)->for($location)->for($user)->create(['dueAt' => now()]);
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->withHeaders($headers)->patch("/api/tasks/reset/{$task->taskID}");

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
    }

    public function testResetNonExistentTask()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);

        $invalidTaskId = 999;

        $response = $this->withHeaders($headers)->put("/api/tasks/{$invalidTaskId}/reset");

        $response->assertStatus(404);
    }

    public function testCopyTaskSetSuccessfully()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $location1 = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $subTask = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();
        $taskToCopy = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create(['isTaskSet' => true, 'shortName' => 'shortName1', 'typeID' => 3]);
        $subTask->taskSetTemplateID = $taskToCopy->taskID;
        $subTask->save();

        $response = $this->withHeaders($headers)->post('/api/tasks/copy', [
            'taskID' => $taskToCopy->taskID,
            'name' => 'New Task Name',
            'locationID' => $location1->locationID,
        ]);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
    }

    public function testCopyTaskSuccessfully()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $location1 = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $subTask = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();
        $taskToCopy = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create(['isTaskSet' => false, 'shortName' => 'shortName1', 'typeID' => 3]);
        $subTask->taskSetTemplateID = $taskToCopy->taskID;
        $subTask->save();

        $response = $this->withHeaders($headers)->post('/api/tasks/copy', [
            'taskID' => $taskToCopy->taskID,
            'name' => 'New Task Name',
            'locationID' => $location1->locationID,
        ]);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
    }

    public function testCopyTaskWithoutLocationSuccessfully()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $subTask = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();
        $taskToCopy = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create(['isTaskSet' => false, 'shortName' => 'shortName1', 'typeID' => 3]);
        $subTask->taskSetTemplateID = $taskToCopy->taskID;
        $subTask->save();

        $response = $this->withHeaders($headers)->post('/api/tasks/copy', [
            'taskID' => $taskToCopy->taskID,
            'name' => 'New Task Name',
        ]);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
    }

    public function testCopyTaskSetWithoutLocationSuccessfully()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $subTask = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();
        $taskToCopy = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create(['isTaskSet' => true, 'shortName' => 'shortName1', 'typeID' => 3]);
        $subTask->taskSetTemplateID = $taskToCopy->taskID;
        $subTask->save();

        $response = $this->withHeaders($headers)->post('/api/tasks/copy', [
            'taskID' => $taskToCopy->taskID,
            'name' => 'New Task Name',
        ]);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
    }

    public function testCopyNonExistentTask()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $subTask = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create();
        $task = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create(['isTaskSet' => true, 'shortName' => 'shortName1', 'typeID' => 3]);
        $subTask->taskSetTemplateID = $task->taskID;
        $subTask->save();

        $invalidTaskId = 999;

        $response = $this->withHeaders($headers)->post('/api/tasks/copy', [
            'taskID' => $invalidTaskId,
            'name' => 'New Task Name',
        ]);

        $response->assertStatus(422);
    }

    public function testDeployTask()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $task = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create(['isTaskSet' => true, 'shortName' => 'shortName1', 'typeID' => 3]);
        $location1 = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $location2 = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $requestData = [
            'taskID' => $task->taskID,
            'locationIDS' => [
                $location1->locationID,
                $location2->locationID,
            ],
            'assignmentOption' => false,
            'notificationOption' => false,
            'scheduleOption1' => true,
            'scheduleOption2' => false,
            'openTasksOption' => false,

        ];

        $this->mock(DeployTaskJob::class, function ($mock) {
            $mock->shouldReceive('dispatch')->andReturnSelf();
        });

        $requestData['locationIDS'] = range(1, config('services.deployTask.jobLimit') + 1);

        $response = $this->withHeaders($headers)->post('/api/deploy-task-taskSet', $requestData);

        $response->assertSuccessful()
            ->assertJsonStructure(['data', 'message'])
            ->assertJson(['message' => 'data is processing through queue job']);

    }

    public function testDeployTaskSingleLocation()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $task = Task::factory()->for($customer)->for($location)->for($dashboard)->for($profile)->create(['isTaskSet' => true, 'shortName' => 'shortName1', 'typeID' => 3]);
        $location1 = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $location2 = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $requestData = [
            'taskID' => $task->taskID,
            'locationIDS' => [
                $location1->locationID,
            ],
            'assignmentOption' => false,
            'notificationOption' => false,
            'scheduleOption1' => true,
            'scheduleOption2' => false,
            'openTasksOption' => false,

        ];

        $this->mock(DeployTaskJob::class, function ($mock) {
            $mock->shouldReceive('dispatch')->andReturnSelf();
        });

        $response = $this->withHeaders($headers)->post('/api/deploy-task-taskSet', $requestData);

        $response->assertSuccessful()
            ->assertJsonCount(2);
    }

    public function testRecallDeployedTaskSuccess()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $taskToRecall = Task::factory()->for($customer)->for($dashboard)->for($location)->for($profile)->create(['shortName' => 'shortName1', 'typeID' => 3, 'dueAt' => now()]);
        $task1 = Task::factory()->for($customer)->for($dashboard)->for($location)->for($profile)->create(['shortName' => 'shortName1', 'typeID' => 3, 'batchID' => $taskToRecall->taskID]);

        $response = $this->withHeaders($headers)->post('/api/recall-deployed-task-taskSet', [
            'taskID' => $taskToRecall->taskID,
            'locationID' => $location->locationID,
            'openTasksOption' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'oldTemplateID' => null,
            'batchID' => $taskToRecall->batchID,
        ]);

    }

    public function testRecallDeployedTaskSetSuccess()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $taskToRecall = Task::factory()->for($customer)->for($dashboard)->for($location)->for($profile)->create(['isTaskSet' => true, 'shortName' => 'shortName1', 'typeID' => 3]);
        $subTask = Task::factory()->for($customer)->for($dashboard)->for($location)->for($profile)->create(['shortName' => 'shortName1', 'typeID' => 3, 'isLocalAddedTask' => true]);
        $subTask->taskSetTemplateID = $taskToRecall->taskID;
        $subTask->save();

        $response = $this->withHeaders($headers)->post('/api/recall-deployed-task-taskSet', [
            'taskID' => $taskToRecall->taskID,
            'locationID' => $location->locationID,
            'openTasksOption' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'oldTemplateID' => null,
            'batchID' => $taskToRecall->batchID,
        ]);
    }

    public function testRecallDeployedTaskWithNoDeployedTaskSuccess()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $taskToRecall = Task::factory()->for($customer)->for($dashboard)->for($profile)->create(['shortName' => 'shortName1', 'typeID' => 3, 'dueAt' => now()]);
        $task1 = Task::factory()->for($customer)->for($dashboard)->for($location)->for($profile)->create(['shortName' => 'shortName1', 'typeID' => 3, 'batchID' => $taskToRecall->taskID]);

        $response = $this->withHeaders($headers)->post('/api/recall-deployed-task-taskSet', [
            'taskID' => $taskToRecall->taskID,
            'locationID' => $location->locationID,
            'openTasksOption' => true,
        ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['message'])
            ->assertJson(['message' => 'Could not get deployed template.']);
    }

    public function testUpdateTaskPriority()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $tasks = Task::factory()->for($customer)->for($dashboard)->for($location)->for($profile)->count(3)->create();

        $updatedPriorities = [
            ['taskID' => $tasks[0]->taskID, 'priority' => 2],
            ['taskID' => $tasks[1]->taskID, 'priority' => 1],
            ['taskID' => $tasks[2]->taskID, 'priority' => 3],
        ];

        $response = $this->withHeaders($headers)->post('/api/tasks/priority', [
            'tasks' => $updatedPriorities,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['data' => true, 'message' => 'Task priority has been updated.']);

        foreach ($updatedPriorities as $updatedTaskPriority) {
            $task = Task::find($updatedTaskPriority['taskID']);
            $this->assertEquals($updatedTaskPriority['priority'], $task->priority);
        }
    }

    public function testUpdateTaskPriorityWithInvalidTaskID()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);

        $invalidTaskID = 999;
        $response = $this->withHeaders($headers)->post('/api/tasks/priority', [
            'tasks' => [['taskID' => $invalidTaskID, 'priority' => 1]],
        ]);

        $response->assertStatus(422);
    }

    public function testSendTaskReminder()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailOther' => false]);
        $headers = $this->authenticateUser($user);
        $request = [
            'to' => [$user->userEmail],
            'subject' => 'Task Reminder Subject',
            'message' => 'Task Reminder Message',
        ];

        $response = $this->withHeaders($headers)->post('/api/tasks/send-reminder', $request);

        $response->assertStatus(200);
        $response->assertJson(['data' => true, 'message' => 'Task reminder sent successfully.']);
    }

    public function testSendTaskReminderWithNotifyEmailOtherDisabled()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailOther' => true]);
        $headers = $this->authenticateUser($user);
        $request = [
            'to' => [$user->userEmail],
            'subject' => 'Task Reminder Subject',
            'message' => 'Task Reminder Message',
        ];

        $response = $this->withHeaders($headers)->post('/api/tasks/send-reminder', $request);

        $response->assertStatus(200);
        $response->assertJson(['data' => true, 'message' => 'Task reminder sent successfully.']);
    }

    public function testSendTaskReminderWithInvalidUser()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $request = [
            'to' => ['invalid_email@example.com'],
            'subject' => 'Task Reminder Subject',
            'message' => 'Task Reminder Message',
        ];

        $response = $this->withHeaders($headers)->post('/api/tasks/send-reminder', $request);

        $response->assertStatus(200);
        $response->assertJson(['data' => true, 'message' => 'Task reminder sent successfully.']);

    }

    public function testUpdateNameAdditionTaskSet()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $subTask = Task::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create(['isTaskSet' => true, 'shortName' => 'shortName1', 'typeID' => 3]);
        $subTask->taskSetTemplateID = $task->taskID;
        $subTask->save();
        $newNameAddition = 'New Name Addition';

        $request = [
            'nameAddition' => $newNameAddition,
        ];

        $response = $this->withHeaders($headers)->patch("/api/tasks/updateNameAddition/{$task->taskID}", $request);

        $response->assertStatus(200);
        $response->assertJson(['data' => true, 'message' => 'Task name addition has been successfully updated.']);

        $this->assertEquals($newNameAddition, $task->fresh()->nameAddition);
    }

    public function testUpdateNameAdditionTask()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $task = Task::factory()->for($customer)->create(['shortName' => 'shortName1', 'typeID' => 3]);
        $newNameAddition = 'New Name Addition';

        $request = [
            'nameAddition' => $newNameAddition,
        ];

        $response = $this->withHeaders($headers)->patch("/api/tasks/updateNameAddition/{$task->taskID}", $request);

        $response->assertStatus(200);
        $response->assertJson(['data' => true, 'message' => 'Task name addition has been successfully updated.']);

        $this->assertEquals($newNameAddition, $task->fresh()->nameAddition);
    }
}
