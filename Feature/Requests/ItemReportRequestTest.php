<?php

namespace Tests\Feature\Requests;

use App\Enum\TaskItemTypeEnum;
use App\Http\Requests\ItemReportRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ItemReportRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new ItemReportRequest();

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
            'itemBatchID' => $taskItem->itemBatchID,
            'name' => 'Sample Report',
        ];

        $validator = Validator::make($validData, (new ItemReportRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'itemBatchID' => 999,
            'name' => 'Sample Report',
        ];

        $validator = Validator::make($invalidData, (new ItemReportRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
