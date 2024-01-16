<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskReoccur;
use App\Models\Tasks\TasksSchedule;
use App\Models\User;
use Tests\TestCase;

class TasksScheduleTest extends TestCase
{
    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $reoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        $tasksSchedule = TasksSchedule::factory()->for($customer)->for($task)->for($reoccur)->create();

        // Retrieve the associated Task through the relationship
        $retrievedTask = $tasksSchedule->task;

        // Assert that the retrieved Task matches the created one
        $this->assertTrue($retrievedTask->is($task));
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $reoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        $tasksSchedule = TasksSchedule::factory()->for($customer)->for($task)->for($reoccur)->create();
        $retrievedCustomer = $tasksSchedule->customer;
        $this->assertTrue($retrievedCustomer->is($customer));
    }

    public function testUserRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $user = User::factory()->for($customer)->create();
        $reoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        $tasksSchedule = TasksSchedule::factory()->for($customer)->for($task)->for($reoccur)->for($user)->create();

        $retrievedUser = $tasksSchedule->user;

        $this->assertTrue($retrievedUser->is($user));
    }

    public function testProfileRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $user = User::factory()->for($customer)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $reoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        $tasksSchedule = TasksSchedule::factory()->for($customer)->for($task)->for($reoccur)->for($user)->for($profile)->create();

        $retrievedProfile = $tasksSchedule->profile;

        $this->assertTrue($retrievedProfile->is($profile));
    }

    public function testTaskReoccurRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $user = User::factory()->for($customer)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $taskReoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        $tasksSchedule = TasksSchedule::factory()->for($customer)->for($task)->for($taskReoccur)->for($user)->for($profile)->create();

        // Retrieve the associated TaskReoccur through the relationship
        $retrievedTaskReoccur = $tasksSchedule->taskReoccur;

        // Assert that the retrieved TaskReoccur matches the created one
        $this->assertTrue($retrievedTaskReoccur->is($taskReoccur));
    }

    public function testScheduleStartAtDisplayAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $user = User::factory()->for($customer)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $taskReoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        $tasksSchedule = TasksSchedule::factory()->for($customer)->for($task)->for($taskReoccur)->for($user)->for($profile)->create();

        // Define your assertions for the scheduleStartAtDisplay attribute
        $timezone = $tasksSchedule->task->location->getTimezone();
        $expectedDisplay = $tasksSchedule->scheduleStartAt
            ->setTimezone($timezone)
            ->format('D-n-j-Y \a\t g:i A T');

        // Retrieve the scheduleStartAtDisplay attribute
        $actualDisplay = $tasksSchedule->scheduleStartAtDisplay;

        // Assert that the retrieved scheduleStartAtDisplay matches the expected value
        $this->assertEquals($expectedDisplay, $actualDisplay);
    }

    public function testTaskDueAtDisplayAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $user = User::factory()->for($customer)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $taskReoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        $schedule = TasksSchedule::factory()->for($customer)->for($task)->for($taskReoccur)->for($user)->for($profile)->create();

        $task->location->timezone = 'America/New_York'; // Replace with the actual timezone

        // Set a taskDueAt attribute
        $schedule->taskDueAt = now(); // You can customize the due date as needed
        $schedule->save();

        // Retrieve the 'taskDueAtDisplay' attribute
        $taskDueAtDisplay = $schedule->taskDueAtDisplay;

        // Convert the taskDueAt to the expected format based on the task's location
        $expectedDisplay = $schedule->taskDueAt
            ->setTimezone($task->location->getTimezone())
            ->format('D-n-j-Y \a\t g:i A T');

        // Assert that the retrieved 'taskDueAtDisplay' matches the expected value
        $this->assertEquals($expectedDisplay, $taskDueAtDisplay);
    }

    public function testBootMethodDeletesTaskReoccur()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $user = User::factory()->for($customer)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $taskReoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        $schedule = TasksSchedule::factory()->for($customer)->for($task)->for($taskReoccur)->for($user)->for($profile)->create();
        $schedule->delete();

        // Check that the related TaskReoccur is also deleted
        $this->assertSoftDeleted($taskReoccur);
    }
}
