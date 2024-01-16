<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetTaskDocumentRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTaskDocumentRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrue()
    {
        $request = new GetTaskDocumentRequest();
        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $validData = [
            'taskID' => $task->taskID,
        ];

        $validator = Validator::make($validData, (new GetTaskDocumentRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'taskID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new GetTaskDocumentRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
