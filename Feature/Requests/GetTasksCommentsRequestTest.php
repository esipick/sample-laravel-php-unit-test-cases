<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetTasksCommentsRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTasksCommentsRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthorizeReturnsTrue()
    {
        $request = new GetTasksCommentsRequest();
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
            'perPage' => 10,
            'orderBy' => 'asc',
            'orderByField' => 'locationID',
            'page' => 1,
        ];

        $validator = Validator::make($validData, (new GetTasksCommentsRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'taskID' => 'not_an_integer', // should be an integer
            'perPage' => 'not_an_integer', // should be an integer
            'orderBy' => 'invalid_order', // should be 'asc' or 'desc'
            'orderByField' => 'invalid_field', // should be a valid field name
            'page' => 'not_an_integer', // should be an integer
        ];

        $validator = Validator::make($invalidData, (new GetTasksCommentsRequest())->rules());

        $this->assertTrue($validator->fails());
    }

    public function testMessagesContainCustomErrorMessages()
    {
        $request = new GetTasksCommentsRequest();

        $customMessages = $request->messages();

        $this->assertEquals('taskID is required.', $customMessages['taskID.required']);
        $this->assertEquals('No comments found against provided taskID.', $customMessages['taskID.exists']);
    }
}
