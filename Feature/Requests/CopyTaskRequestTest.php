<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\CopyTaskRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CopyTaskRequestTest extends TestCase
{
    public function testAuthorizeAlwaysReturnsTrue()
    {
        $request = new CopyTaskRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $validData = [
            'name' => 'ValidTaskName',
            'locationID' => $location->locationID,
            'taskID' => $task->taskID,
        ];

        $validator = Validator::make($validData, (new CopyTaskRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'name' => null, // Missing required field
            'locationID' => 'not_an_integer',
            'taskID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new CopyTaskRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
