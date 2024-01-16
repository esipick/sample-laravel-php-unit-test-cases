<?php

namespace Tests\Feature\Requests;

use App\Enum\TaskItemTypeEnum;
use App\Http\Requests\AddTaskItemOptionRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AddTaskItemOptionRequestTest extends TestCase
{
    public function testAuthorizeAlwaysReturnsTrue()
    {
        $request = new AddTaskItemOptionRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create(
            ['itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value]
        );

        $validData = [
            'itemID' => $taskItem->itemID,
            'prompt' => 'Valid Prompt',
        ];

        $validator = Validator::make($validData, (new AddTaskItemOptionRequest())->rules());
        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationPassesWithMissingPrompt()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create(
            ['itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value]
        );

        $validDataWithoutPrompt = [
            'itemID' => $taskItem->itemID,
        ];

        $validator = Validator::make($validDataWithoutPrompt, (new AddTaskItemOptionRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'itemID' => 'not_an_integer',
            'prompt' => str_repeat('a', 1001), // Exceeds the max length
        ];

        $validator = Validator::make($invalidData, (new AddTaskItemOptionRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
