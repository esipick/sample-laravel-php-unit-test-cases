<?php

namespace Tests\Feature\Requests;

use App\Enum\TaskItemTypeEnum;
use App\Http\Requests\GetTaskItemDocumentRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTaskItemDocumentRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrue()
    {
        $request = new GetTaskItemDocumentRequest();
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
        ];

        $validator = Validator::make($validData, (new GetTaskItemDocumentRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'itemID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new GetTaskItemDocumentRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
