<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskAuditResponselog;
use App\Models\Tasks\TaskItem;
use App\Models\User;
use Tests\TestCase;

class TaskAuditResponselogTest extends TestCase
{
    public function testCreateTaskAuditResponselog()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer))->create();
        $taskItem = $task->taskItems()->first();
        $responselog = TaskAuditResponselog::factory()->for($task)->for($customer)->for($user)->for($taskItem)->create();

        $this->assertInstanceOf(TaskAuditResponselog::class, $responselog);
        $this->assertDatabaseHas('tasks_audit_responselog', ['responseLogID' => $responselog->responseLogID]);
    }

    public function testTaskItemRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer))->create();
        $taskItem = $task->taskItems()->first();
        $responselog = TaskAuditResponselog::factory()->for($task)->for($customer)->for($user)->for($taskItem)->create();
        $this->assertInstanceOf(TaskItem::class, $responselog->taskItem);
    }

    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer))->create();
        $taskItem = $task->taskItems()->first();
        $responselog = TaskAuditResponselog::factory()->for($task)->for($customer)->for($user)->for($taskItem)->create();
        $this->assertInstanceOf(Task::class, $responselog->task);
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer))->create();
        $taskItem = $task->taskItems()->first();
        $responselog = TaskAuditResponselog::factory()->for($task)->for($customer)->for($user)->for($taskItem)->create();
        $this->assertInstanceOf(Customer::class, $responselog->customer);
    }

    public function testUserRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer))->create();
        $taskItem = $task->taskItems()->first();
        $responselog = TaskAuditResponselog::factory()->for($task)->for($customer)->for($user)->for($taskItem)->create();
        $this->assertInstanceOf(User::class, $responselog->user);
    }

    public function testFillableFields()
    {
        $responselog = new TaskAuditResponselog;
        $fillable = ['response', 'responseLogDate', 'responseLogNotes', 'itemID', 'userID', 'taskID', 'customerID'];
        $notFillable = array_diff($responselog->getFillable(), $fillable);

        $this->assertEquals($fillable, $responselog->getFillable());
        $this->assertEmpty($notFillable);
    }
}
