<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskFile;
use App\Models\Tasks\TaskItem;
use Tests\TestCase;

class TaskFileTest extends TestCase
{
    /**
     * Test creating a TaskFile.
     */
    public function testCreateTaskFile()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer)->count(5))->create();
        $taskItem = $task->taskItems()->first();

        $taskFile = TaskFile::create([
            'fileName' => 'example.pdf',
            'uploadFileName' => 'example_uploaded.pdf',
            'taskID' => $task->taskID,
            'itemID' => $taskItem->itemID,
            'customerID' => $customer->customerID,
        ]);

        $this->assertInstanceOf(TaskFile::class, $taskFile);
        $this->assertEquals('example.pdf', $taskFile->fileName);
        $this->assertEquals('example_uploaded.pdf', $taskFile->uploadFileName);
    }

    /**
     * Test updating a TaskFile's attributes.
     */
    public function testUpdateTaskFile()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer)->count(5))->create();
        $taskItem = $task->taskItems()->first();

        $taskFile = TaskFile::factory()->for($customer)->for($taskItem)->for($task)->create();
        $newFileName = 'updated.pdf';

        $taskFile->update([
            'fileName' => $newFileName,
        ]);

        $updatedTaskFile = TaskFile::find($taskFile->fileID);

        $this->assertEquals($newFileName, $updatedTaskFile->fileName);
    }

    /**
     * Test deleting a TaskFile.
     */
    public function testDeleteTaskFile()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer)->count(5))->create();
        $taskItem = $task->taskItems()->first();
        $taskFile = TaskFile::factory()->for($customer)->for($taskItem)->for($task)->create();

        $this->assertTrue($taskFile->delete());
        $this->assertNull(TaskFile::find($taskFile->fileID));
    }

    /**
     * Test the task relationship.
     */
    public function testTaskRelationship()
    {

        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer)->count(5))->create();
        $taskItem = $task->taskItems()->first();
        $taskFile = TaskFile::factory()->for($customer)->for($taskItem)->for($task)->create();

        $this->assertInstanceOf(Task::class, $taskFile->task);
        $this->assertEquals($task->taskID, $taskFile->task->taskID);
    }

    /**
     * Test the task item relationship.
     */
    public function testTaskItemRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer)->count(5))->create();
        $taskItem = $task->taskItems()->first();
        $taskFile = TaskFile::factory()->for($customer)->for($taskItem)->for($task)->create();

        $this->assertInstanceOf(TaskItem::class, $taskFile->taskItem);
        $this->assertEquals($taskItem->itemID, $taskFile->taskItem->itemID);
    }

    /**
     * Test the customer relationship.
     */
    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer)->count(5))->create();
        $taskItem = $task->taskItems()->first();
        $taskFile = TaskFile::factory()->for($customer)->for($taskItem)->for($task)->create();

        $this->assertInstanceOf(Customer::class, $taskFile->customer);
        $this->assertEquals($customer->customerID, $taskFile->customer->customerID);
    }
}
