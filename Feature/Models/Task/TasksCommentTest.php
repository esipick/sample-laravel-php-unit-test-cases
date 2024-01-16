<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksComment;
use App\Models\User;
use Tests\TestCase;

class TasksCommentTest extends TestCase
{
    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $taskComment = TasksComment::factory()->for($task)->for($user)->create();
        $retrievedTask = $taskComment->task;

        $this->assertTrue($retrievedTask->is($task));
    }

    public function testUserRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $taskComment = TasksComment::factory()->for($task)->for($user)->create();

        // Retrieve the associated User through the relationship
        $retrievedUser = $taskComment->user;

        // Assert that the retrieved User matches the created one
        $this->assertTrue($retrievedUser->is($user));
    }
}
