<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetColorsAtCompletionTimeRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksAssessmentInfo;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetColorsAtCompletionTimeRequestTest extends TestCase
{
    public function testAuthorizeReturnsFalseWhenUserIsNotAuthorized()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        Auth::shouldReceive('user')->andReturn(null);

        $request = new GetColorsAtCompletionTimeRequest(['locationID' => $location->locationID]);

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

        $request = new GetColorsAtCompletionTimeRequest(['locationID' => $location->locationID]);

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

        $request = new GetColorsAtCompletionTimeRequest(['locationID' => $location->locationID]);

        $this->assertFalse($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $topic = Topic::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $validData = [
            'startDate' => '2023-01-01',
            'endDate' => '2023-12-31',
            'topicID' => $topic->topicID,
            'locationID' => $location->locationID,
        ];
        Auth::shouldReceive('user')->andReturn($user);
        $validator = Validator::make($validData, (new GetColorsAtCompletionTimeRequest(['locationID' => $location->locationID]))->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'startDate' => 'invalid_date_format',
            'endDate' => 'invalid_date_format',
            'topicID' => 'not_an_integer',
            'locationID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new GetColorsAtCompletionTimeRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
