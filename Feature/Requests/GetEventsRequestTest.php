<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetDocumentRequest;
use App\Http\Requests\GetEventsRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksAssessmentInfo;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetEventsRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthorizeReturnsFalseWhenUserIsNotAuthorized()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        Auth::shouldReceive('user')->andReturn(null);

        $request = new GetEventsRequest(['locationID' => $location->locationID]);

        $this->assertFalse($request->authorize());
    }

    public function testAuthorizeReturnsTrueWhenUserIsAuthorized()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        TasksAssessmentInfo::factory()->for($task)->for($user)->create();
        Auth::shouldReceive('user')->andReturn($user);

        $request = new GetEventsRequest(['locationID' => $location->locationID]);

        $this->assertTrue($request->authorize());
    }
    public function testAuthorizeReturnsTrueWhenUserIsAuthorizedInvalidCustomer()
    {
        $customer = Customer::factory()->create();
        $customer1 = Customer::factory()->create();
        $location = Location::factory()->for($customer1)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        TasksAssessmentInfo::factory()->for($task)->for($user)->create();
        Auth::shouldReceive('user')->andReturn($user);

        $request = new GetEventsRequest(['locationID' => $location->locationID]);

        $this->assertFalse($request->authorize());
    }
    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $topic = Topic::factory()->for($customer)->create();
        $validData = [
            'search' => 'example',
            'locationID' => $location->locationID,
            'topicID' => $topic->topicID,
            'orderBy' => 'asc',
            'orderByField' => 'taskID',
        ];

        $validator = Validator::make($validData, (new GetEventsRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'search' => 123,
            'locationID' => 'not_an_integer',
            'topicID' => 'not_an_integer',
            'orderBy' => 'invalid_order',
            'orderByField' => 'invalid_field',
        ];

        $validator = Validator::make($invalidData, (new GetEventsRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
