<?php

namespace Feature\Models\Task;

use App\Enum\TaskItemTypeEnum;
use App\Http\Resources\TaskItemCollection;
use App\Models\Customer;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskAuditResponselog;
use App\Models\Tasks\TaskFile;
use App\Models\Tasks\TaskItem;
use App\Models\Tasks\TaskItemOption;
use App\Models\Tasks\TaskItemType;
use App\Models\User;
use Tests\TestCase;

class TaskItemTest extends TestCase
{
    public function testNewCollection()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $this->assertInstanceOf(TaskItemCollection::class, $taskItem->newCollection());
    }

    public function testBelongsToRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        $taskItemType = TaskItemType::factory()->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->for($taskItemType)->create(['taskToSpawn' => $task, 'response' => $task]);

        $this->assertInstanceOf(Task::class, $taskItem->taskFromResponse);
        $this->assertInstanceOf(Task::class, $taskItem->task);
        $this->assertInstanceOf(TaskItemType::class, $taskItem->taskItemType);
        $this->assertInstanceOf(Customer::class, $taskItem->customer);
        $this->assertInstanceOf(Customer::class, $taskItem->customer);
    }

    public function testHasManyRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        TaskItemOption::factory()->for($customer)->for($taskItem)->create();
        $this->assertInstanceOf(TaskItemOption::class, $taskItem->taskItemOptions[0]);

        TaskFile::factory()->for($customer)->for($task)->for($taskItem)->create();
        $this->assertInstanceOf(TaskFile::class, $taskItem->taskFiles[0]);

        TaskAuditResponselog::factory()->for($taskItem)->for($user)->for($task)->for($customer)->create();
        $this->assertInstanceOf(TaskAuditResponselog::class, $taskItem->taskAuditResponselogs[0]);

        TaskItem::factory()->for($customer)->for($task)->create(['parentTaskItemID' => $taskItem->taskItemID]);
        $this->assertInstanceOf(TaskItem::class, $taskItem->children[0]);
    }

    public function testIsCompletedForAssignTaskWithDueAtAndTaskToSpawn()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create(['dueAt' => null]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create([
            'itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value,
            'taskToSpawn' => 1,
        ]);

        $this->assertTrue($taskItem->isCompleted);
    }

    public function testIsCompletedForAssignTaskWithoutDueAtAndNoTaskToSpawn()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create(['dueAt' => null]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create([
            'itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value,
            'taskToSpawn' => null,
        ]);

        $this->assertFalse($taskItem->isCompleted);
    }

    public function testIsCompletedForOtherItemTypesWithResponse()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create(['dueAt' => null]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create([
            'itemTypeID' => TaskItemTypeEnum::APPENDTOTITLE->value,
            'taskToSpawn' => null,
            'response' => 'Some response',
        ]);

        $this->assertTrue($taskItem->isCompleted);
    }

    public function testIsCompletedForOtherItemTypesWithoutResponse()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create(['dueAt' => null]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create([
            'itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value,
            'taskToSpawn' => null,
            'response' => null,
        ]);

        $this->assertFalse($taskItem->isCompleted);
    }

    public function testVerifyKeyName()
    {

        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $this->assertEquals($taskItem->parentTaskItemID, $taskItem[$taskItem->getParentKeyName()]);
        $this->assertEquals($taskItem->taskItemID, $taskItem[$taskItem->getLocalKeyName()]);
    }

    public function testTaskToSpawnRelationship()
    {
        $customer = Customer::factory()->create();
        $taskA = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer))->create();
        $taskB = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($taskB)->create(['taskToSpawn' => $taskA->batchID]);
        $associatedTask = $taskItem->taskToSpawn()->first();
        $this->assertInstanceOf(Task::class, $associatedTask);
        $this->assertEquals($taskA->batchID, $taskItem->taskToSpawn);

    }

    public function testAssignedTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $taskA = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer))->create();
        $taskB = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($taskB)->create(['taskToSpawn' => $taskA->batchID]);
        $associatedTask = $taskItem->assignedTask;

        $this->assertInstanceOf(Task::class, $associatedTask);
        $this->assertEquals($taskA->batchID, $taskItem->taskToSpawn);
    }
}
