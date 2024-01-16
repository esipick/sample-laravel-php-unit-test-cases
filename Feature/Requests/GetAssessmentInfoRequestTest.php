<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetAssessmentInfoRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksAssessmentInfo;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetAssessmentInfoRequestTest extends TestCase
{
    public function testAuthorizeAlwaysReturnsTrue()
    {
        $request = new GetAssessmentInfoRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        TasksAssessmentInfo::factory()->for($task)->for($user)->create();
        $validData = [
            'taskID' => $task->taskID,
        ];

        $validator = Validator::make($validData, (new GetAssessmentInfoRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'taskID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new GetAssessmentInfoRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
