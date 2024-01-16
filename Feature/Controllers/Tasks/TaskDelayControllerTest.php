<?php

namespace Tests\Feature\Controllers;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskDelay;
use App\Models\User;
use Tests\TestCase;

class TaskDelayControllerTest extends TestCase
{
    /** @test */
    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $requestData = [
            'reason' => 'sample test for reason',
            'taskID' => $task->taskID,
            'delayEndAt' => date('Y-m-d H:i:s'),
        ];

        $response = $this->withHeaders($headers)->post('/api/task-delays', $requestData);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    /** @test */
    public function testUpdateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskDelay = TaskDelay::factory()->for($user)->for($customer)->for($task)->create();

        $requestData = [
            'reason' => 'sample updated text',
            'taskID' => $task->taskID,
            'delayEndAt' => date('Y-m-d H:i:s'),
        ];

        $response = $this->withHeaders($headers)->put('/api/task-delays/'.$taskDelay->delayID, $requestData);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    /** @test */
    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskDelay = TaskDelay::factory()->for($user)->for($customer)->for($task)->create();

        $response = $this->withHeaders($headers)->delete('/api/task-delays/'.$taskDelay->delayID);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }
}
