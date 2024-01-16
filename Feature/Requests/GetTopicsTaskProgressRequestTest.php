<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetTopicsTaskProgressRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksAssessmentInfo;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTopicsTaskProgressRequestTest extends TestCase
{
    public function testAuthorizeReturnsFalseWhenUserIsNotAuthorized()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        Auth::shouldReceive('user')->andReturn(null);

        $request = new GetTopicsTaskProgressRequest(['locationID' => $location->locationID]);

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

        $request = new GetTopicsTaskProgressRequest(['locationID' => $location->locationID]);

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

        $request = new GetTopicsTaskProgressRequest(['locationID' => $location->locationID]);

        $this->assertFalse($request->authorize());
    }
    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $validData = [
            'locationID' => $location->locationID,
        ];

        $validator = Validator::make($validData, (new GetTopicsTaskProgressRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $customer = Customer::factory()->create();
        $topic = Topic::factory()->for($customer)->create();

        $invalidData = [
            'topicID' => $topic->topicID,
            'locationID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new GetTopicsTaskProgressRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
