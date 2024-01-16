<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetCompletedTasksRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksAssessmentInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetCompletedTasksRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthorizeReturnsFalseWhenUserIsNotAuthorized()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        Auth::shouldReceive('user')->andReturn(null);

        $request = new GetCompletedTasksRequest(['locationID' => $location->locationID]);

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

        $request = new GetCompletedTasksRequest(['locationID' => $location->locationID]);

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

        $request = new GetCompletedTasksRequest(['locationID' => $location->locationID]);

        $this->assertFalse($request->authorize());
    }
    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $validData = [
            'assignedTo' => $user->userID,
            'dueDateStart' => '2023-01-01',
            'dueDateEnd' => '2023-12-31',
            'completedDateStart' => '2023-01-01',
            'completedDateEnd' => '2023-12-31',
            'search' => 'example',
            'locationID' => $location->locationID,
            'orderBy' => 'asc',
            'orderByField' => 'taskID',
            'startDate' => '2023-01-01',
            'endDate' => '2023-12-31',
            'hasCompletedTasksFromTaskSets' => true,
        ];

        $validator = Validator::make($validData, (new GetCompletedTasksRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'assignedTo' => 'not_an_integer',
            'dueDateStart' => 'invalid_date_format',
            'dueDateEnd' => 'invalid_date_format',
            'completedDateStart' => 'invalid_date_format',
            'completedDateEnd' => 'invalid_date_format',
            'search' => 123,
            'locationID' => 'not_an_integer',
            'orderBy' => 'invalid_order',
            'orderByField' => 'invalid_field',
            'startDate' => 'invalid_date_format',
            'endDate' => 'invalid_date_format',
            'hasCompletedTasksFromTaskSets' => 'not_a_boolean',
        ];

        $validator = Validator::make($invalidData, (new GetCompletedTasksRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
