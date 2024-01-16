<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskDelay;
use App\Models\User;
use Tests\TestCase;

class TaskDelayTest extends TestCase
{
    public function testCanCreateTaskDelay()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        $taskDelay = TaskDelay::factory()->for($user)->for($customer)->for($task)->create();

        $this->assertInstanceOf(TaskDelay::class, $taskDelay);
    }

    public function testCanRetrievesRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        $taskDelay = TaskDelay::factory()->for($user)->for($customer)->for($task)->create();

        $this->assertInstanceOf(Task::class, $taskDelay->task);
        $this->assertInstanceOf(Customer::class, $taskDelay->customer);
        $this->assertInstanceOf(User::class, $taskDelay->user);
    }
}
