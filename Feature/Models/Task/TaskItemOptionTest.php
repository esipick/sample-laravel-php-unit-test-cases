<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use App\Models\Tasks\TaskItemOption;
use Tests\TestCase;

class TaskItemOptionTest extends TestCase
{
    public function testCanCreateTaskItemOption()
    {
        $insertData = [
            'order' => 1,
            'itemOptionID' => 419,
            'prompt' => 'Lorem Ipsum',
        ];

        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $taskItemOption = TaskItemOption::factory()->for($customer)->for($taskItem)->create($insertData);

        $this->assertEquals($insertData['order'], $taskItemOption->order);
        $this->assertEquals($insertData['itemOptionID'], $taskItemOption->itemOptionID);
        $this->assertEquals($insertData['prompt'], $taskItemOption->prompt);
    }

    public function testCanUpdateTaskItemOption()
    {
        $insertData = [
            'order' => 159,
            'itemOptionID' => 354,
            'prompt' => 'Lorem Ipsum',
        ];

        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $taskItemOption = TaskItemOption::factory()->for($customer)->for($taskItem)->create($insertData);

        $updatedData = [
            'order' => 258,
            'itemOptionID' => 419,
            'prompt' => 'Hello World',
        ];

        $taskItemOption->update($updatedData);

        $this->assertEquals($updatedData['order'], $taskItemOption->order);
        $this->assertEquals($updatedData['itemOptionID'], $taskItemOption->itemOptionID);
        $this->assertEquals($updatedData['prompt'], $taskItemOption->prompt);
    }

    public function testRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $taskItemOption = TaskItemOption::factory()->for($customer)->for($taskItem)->create();

        $this->assertInstanceOf(Customer::class, $taskItemOption->customer);
        $this->assertInstanceOf(TaskItem::class, $taskItemOption->taskItem);
    }
}
