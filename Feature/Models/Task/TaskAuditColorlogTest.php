<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskAuditColorlog;
use App\Models\User;
use Tests\TestCase;

class TaskAuditColorlogTest extends TestCase
{
    public function testCreateTaskAuditColorlog()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $colorLog = TaskAuditColorlog::factory()->for($customer)->for($task)->create();

        $this->assertInstanceOf(TaskAuditColorlog::class, $colorLog);
        $this->assertDatabaseHas('tasks_audit_colorlog', ['logID' => $colorLog->logID]);
    }

    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $colorLog = TaskAuditColorlog::factory()->for($customer)->for($task)->create();
        $this->assertInstanceOf(Task::class, $colorLog->task);
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $colorLog = TaskAuditColorlog::factory()->for($customer)->for($task)->create();
        $this->assertInstanceOf(Customer::class, $colorLog->customer);
    }

    public function testFillableFields()
    {
        $colorLog = new TaskAuditColorlog;
        $fillable = ['logDate', 'logNewColor', 'logNote', 'logOldColor', 'taskID', 'customerID'];
        $notFillable = array_diff($colorLog->getFillable(), $fillable);

        $this->assertEquals($fillable, $colorLog->getFillable());
        $this->assertEmpty($notFillable);
    }
}
