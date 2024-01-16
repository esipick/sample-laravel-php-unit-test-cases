<?php

namespace Feature\Models\Task;

use App\Models\Tasks\TaskItemType;
use Tests\TestCase;

class TaskItemTypeTest extends TestCase
{
    public function testCanCreateTaskItemType()
    {
        $taskItemTypeData = [
            'itemTypeName' => 'Test Type',
            'itemTypeDescription' => 'Description for test type',
            'deprecated' => false,
        ];

        $taskItemType = TaskItemType::create($taskItemTypeData);

        $this->assertInstanceOf(TaskItemType::class, $taskItemType);
        $this->assertEquals($taskItemTypeData['itemTypeName'], $taskItemType->itemTypeName);
        $this->assertEquals($taskItemTypeData['itemTypeDescription'], $taskItemType->itemTypeDescription);
        $this->assertEquals($taskItemTypeData['deprecated'], $taskItemType->deprecated);

    }

    public function testCanUpdateTaskItemType()
    {
        $taskItemTypeData = [
            'itemTypeName' => 'Test Type',
            'itemTypeDescription' => 'Description for test type',
            'deprecated' => false,
        ];

        $taskItemType = TaskItemType::create($taskItemTypeData);

        $updatedData = [
            'itemTypeName' => 'Updated Type Name',
            'itemTypeDescription' => 'Updated description',
            'deprecated' => true,
        ];

        $taskItemType->update($updatedData);

        $this->assertEquals($updatedData['itemTypeName'], $taskItemType->itemTypeName);
        $this->assertEquals($updatedData['itemTypeDescription'], $taskItemType->itemTypeDescription);
        $this->assertEquals($updatedData['deprecated'], $taskItemType->deprecated);
    }
}
