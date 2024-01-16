<?php

namespace Feature\Controllers\Tasks;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class SpawnedControllerTest extends TestCase
{
    public function testSpawnedTaskWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $task = Task::factory()->for($customer)->for($location)->for($profile)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $validTaskData = [
            'itemID' => $taskItem->itemID,
            'taskID' => $task->taskID,
            'locationID' => $location->locationID,
            'userID' => $user->userID,
            'profileID' => $profile->profileID,
            'timestamp' => $timestamp,
            'timeIntervalID' => 1,
        ];

        $response = $this->withHeaders($headers)->post('/api/tasks/spawned-task', $validTaskData);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);

    }

    public function testSpawnedTaskWithBatchID()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $task1 = Task::factory()->for($customer)->for($location)->for($profile)->create();
        $task = Task::factory()->for($customer)->for($location)->for($profile)->create();
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $validTaskData = [
            'batchID' => $task1->batchID,
            'taskID' => $task->taskID,
            'locationID' => $location->locationID,
            'userID' => $user->userID,
            'profileID' => $profile->profileID,
            'timestamp' => $timestamp,
            'timeIntervalID' => 1,
        ];

        $response = $this->withHeaders($headers)->post('/api/tasks/spawned-task', $validTaskData);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);

    }

    public function testSpawnedTaskWithInvalidTaskBatchID()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $task1 = Task::factory()->for($customer)->for($profile)->create();
        $task = Task::factory()->for($customer)->for($location)->for($profile)->create();
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $validTaskData = [
            'batchID' => $task1->batchID,
            'taskID' => $task->taskID,
            'locationID' => $location->locationID,
            'userID' => $user->userID,
            'profileID' => $profile->profileID,
            'timestamp' => $timestamp,
            'timeIntervalID' => 1,
        ];

        $response = $this->withHeaders($headers)->post('/api/tasks/spawned-task', $validTaskData);
        $response->assertStatus(500);

    }

    public function testSpawnedTaskWithInvalidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $headers = $this->authenticateUser($user);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $timestamp = Carbon::now()->format('Y-m-d H:i:s');
        $validTaskData = [
            'taskID' => 2222,
            'locationID' => $location->locationID,
            'userID' => $user->userID,
            'profileID' => $profile->profileID,
            'timestamp' => $timestamp,
            'timeIntervalID' => 1,
        ];

        $response = $this->withHeaders($headers)->post('/api/tasks/spawned-task', $validTaskData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'taskID',
        ]);
    }
}
