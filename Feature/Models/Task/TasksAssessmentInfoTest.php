<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksAssessmentInfo;
use App\Models\User;
use Tests\TestCase;

class TasksAssessmentInfoTest extends TestCase
{
    public function testCreateTasksAssessmentInfo()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $assessmentInfo = TasksAssessmentInfo::factory()->for($task)->for($user)->create();

        $this->assertInstanceOf(TasksAssessmentInfo::class, $assessmentInfo);
    }

    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $assessmentInfo = TasksAssessmentInfo::factory()->for($task)->for($user)->create();

        $this->assertInstanceOf(Task::class, $assessmentInfo->task);
    }

    public function testUserRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $assessmentInfo = TasksAssessmentInfo::factory()->for($task)->for($user)->create();

        $this->assertInstanceOf(User::class, $assessmentInfo->user);
    }
}
