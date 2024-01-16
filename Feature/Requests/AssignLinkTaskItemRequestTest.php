<?php

namespace Tests\Feature\Requests;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Requests\AssignLinkTaskItemRequest;
use Illuminate\Support\Facades\Validator;

class AssignLinkTaskItemRequestTest extends TestCase
{
    use RefreshDatabase;
    public function testAuthorizeAlwaysReturnsTrue()
    {
        $request = new AssignLinkTaskItemRequest();

        $this->assertTrue($request->authorize());
    }
    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $validData = [
            'taskID' =>  $task->taskID,
            'linkedTaskID' => $task->taskID,
        ];

        $validator = Validator::make($validData, (new AssignLinkTaskItemRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'taskID' => 'not_an_integer',
            'linkedTaskID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new AssignLinkTaskItemRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
