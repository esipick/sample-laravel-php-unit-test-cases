<?php

namespace Tests\Feature\Requests;

use App\Enum\TaskItemTypeEnum;
use App\Http\Requests\StoreTaskItemRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTaskItemRequestTest extends TestCase
{

    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreTaskItemRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $validData = [
            'taskID' => $task->taskID,
            'itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value,
            'parentTaskItemID' => null,
            'parentItemOptionID' => null,
            'prompt' => 'Valid prompt',
        ];

        $validator = Validator::make($validData, (new StoreTaskItemRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRequiredFieldsAreChecked()
    {
        $invalidData = [
            // Missing required 'taskID' field
            'itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value,
            'parentTaskItemID' => null,
            'parentItemOptionID' => null,
            'prompt' => 'Valid prompt',
        ];

        $validator = Validator::make($invalidData, (new StoreTaskItemRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('taskID', $validator->errors()->toArray());

    }

    public function testInvalidDataFailsValidation()
    {
        $invalidData = [
            'taskID' => 'invalid', // 'taskID' should be an integer
            'itemTypeID' => 'invalid', // 'itemTypeID' should be an integer
            'parentTaskItemID' => 'invalid', // 'parentTaskItemID' should be an integer or null
            'parentItemOptionID' => 'invalid', // 'parentItemOptionID' should be an integer or null
            'prompt' => str_repeat('a', 1001), // 'prompt' should be less than or equal to 1000 characters
        ];

        $validator = Validator::make($invalidData, (new StoreTaskItemRequest())->rules());

        $this->assertTrue($validator->fails());
    }

    public function testNonExistingTaskFailsValidation()
    {
        $nonExistingData = [
            'taskID' => 999, // Assuming this task ID does not exist
            'itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value,
            'parentTaskItemID' => null,
            'parentItemOptionID' => null,
            'prompt' => 'Valid prompt',
        ];

        $validator = Validator::make($nonExistingData, (new StoreTaskItemRequest())->rules());

        $this->assertTrue($validator->fails());
    }

    public function testNonExistingItemTypeFailsValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $nonExistingData = [
            'taskID' => $task->taskID,
            'itemTypeID' => 999, // Assuming this item type ID does not exist
            'parentTaskItemID' => null,
            'parentItemOptionID' => null,
            'prompt' => 'Valid prompt',
        ];

        $validator = Validator::make($nonExistingData, (new StoreTaskItemRequest())->rules());

        $this->assertTrue($validator->fails());
    }

}
