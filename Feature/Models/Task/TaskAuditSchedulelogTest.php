<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskAuditSchedulelog;
use App\Models\Tasks\TaskReoccur;
use App\Models\User;
use Tests\TestCase;

class TaskAuditSchedulelogTest extends TestCase
{
    public function testCreateTaskAuditSchedulelog()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();
        $scheduleLog = TaskAuditSchedulelog::factory()->for($customer)->for($user)->for($taskReoccur)->create();

        $this->assertInstanceOf(TaskAuditSchedulelog::class, $scheduleLog);
        $this->assertDatabaseHas('tasks_audit_schedulelog', ['scheduleLogID' => $scheduleLog->scheduleLogID]);
    }

    public function testTaskReoccurRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();
        $scheduleLog = TaskAuditSchedulelog::factory()->for($customer)->for($user)->for($taskReoccur)->create();

        $this->assertInstanceOf(TaskReoccur::class, $scheduleLog->taskReoccur);
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();
        $scheduleLog = TaskAuditSchedulelog::factory()->for($customer)->for($user)->for($taskReoccur)->create();

        $this->assertInstanceOf(Customer::class, $scheduleLog->customer);
    }

    public function testUserRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();
        $scheduleLog = TaskAuditSchedulelog::factory()->for($customer)->for($user)->for($taskReoccur)->create();

        $this->assertInstanceOf(User::class, $scheduleLog->user);
    }

    public function testFillableFields()
    {
        $scheduleLog = new TaskAuditSchedulelog;
        $fillable = ['scheduleLogDate', 'scheduleLogNotes', 'scheduleLogOnOff', 'userID', 'reoccurID', 'customerID'];
        $notFillable = array_diff($scheduleLog->getFillable(), $fillable);

        $this->assertEquals($fillable, $scheduleLog->getFillable());
        $this->assertEmpty($notFillable);
    }
}
