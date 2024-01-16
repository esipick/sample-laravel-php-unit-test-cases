<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskAuditAssignmentlog;
use App\Models\User;
use Tests\TestCase;

class TaskAuditAssignmentlogTest extends TestCase
{
    public function testCreateTaskAuditAssignmentlog()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $assignmentLog = TaskAuditAssignmentlog::factory()->for($task)->for($customer)->for($user)->create();

        $this->assertInstanceOf(TaskAuditAssignmentlog::class, $assignmentLog);
        $this->assertDatabaseHas('tasks_audit_assignmentlog', ['assignmentLogID' => $assignmentLog->assignmentLogID]);
    }

    public function testAssignedByRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $assignmentLog = TaskAuditAssignmentlog::factory()->for($task)->for($customer)->for($user)->for($user, 'assignedBy')->create();
        $this->assertInstanceOf(User::class, $assignmentLog->assignedBy);
    }

    public function testOldOwnerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $assignmentLog = TaskAuditAssignmentlog::factory()->for($task)->for($customer)->for($user)->for($user, 'oldOwner')->create();
        $this->assertInstanceOf(User::class, $assignmentLog->oldOwner);
    }

    public function testNewOwnerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $assignmentLog = TaskAuditAssignmentlog::factory()->for($task)->for($customer)->for($user)->for($user, 'newOwner')->create();
        $this->assertInstanceOf(User::class, $assignmentLog->newOwner);
    }

    public function testNewOwnerProfileRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $assignmentLog = TaskAuditAssignmentlog::factory()->for($task)->for($customer)->for($user)->for($profile, 'newOwnerProfile')->create();
        $this->assertInstanceOf(Profile::class, $assignmentLog->newOwnerProfile);
    }

    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $assignmentLog = TaskAuditAssignmentlog::factory()->for($task)->for($customer)->for($user)->for($profile, 'newOwnerProfile')->create();
        $this->assertInstanceOf(Task::class, $assignmentLog->task);
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $assignmentLog = TaskAuditAssignmentlog::factory()->for($task)->for($customer)->for($user)->for($profile, 'newOwnerProfile')->create();
        $this->assertInstanceOf(Customer::class, $assignmentLog->customer);
    }

    public function testFillableFields()
    {
        $assignmentLog = new TaskAuditAssignmentlog;
        $fillable = ['assignmentLogDate', 'assignmentLogNotes', 'assignmentNewOwner', 'assignmentNewProfile', 'assignmentOldOwner', 'assignmentOldProfile', 'userID', 'taskID', 'customerID'];
        $notFillable = array_diff($assignmentLog->getFillable(), $fillable);

        $this->assertEquals($fillable, $assignmentLog->getFillable());
        $this->assertEmpty($notFillable);
    }
}
