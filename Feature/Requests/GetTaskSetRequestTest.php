<?php

namespace Tests\Feature\Requests;

use App\Enum\TaskType;
use App\Http\Requests\GetTaskSetRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksAssessmentInfo;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTaskSetRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthorizeReturnsFalseWhenUserIsNotAuthorized()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        Auth::shouldReceive('user')->andReturn(null);

        $request = new GetTaskSetRequest(['locationID' => $location->locationID]);

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

        $request = new GetTaskSetRequest(['locationID' => $location->locationID]);

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

        $request = new GetTaskSetRequest(['locationID' => $location->locationID]);

        $this->assertFalse($request->authorize());
    }


    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $topic = Topic::factory()->for($customer)->create();

        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $validData = [
            'search' => 'example_search',
            'openTaskSets' => true,
            'deployed' => false,
            'spawnedTasks' => true,
            'myTasks' => false,
            'globalTaskSets' => true,
            'globalDashboardTaskSets' => false,
            'nativeTasks' => true,
            'topicID' => $topic->topicID,
            'perPage' => 10,
            'orderBy' => 'asc',
            'orderByField' => 'taskID',
            'page' => 1,
            'locationID' => $location->locationID,
            'dashboardID' => $dashboard->dashboardID,
            'userID' => $user->userID,
            'type' => TaskType::ASSESSMENT->value,
        ];

        $validator = Validator::make($validData, (new GetTaskSetRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {


        $invalidData = [
            'search' => 123, // should be a string
            'openTaskSets' => 'not_a_boolean', // should be a boolean
            'deployed' => 'not_a_boolean', // should be a boolean
            'spawnedTasks' => 'not_a_boolean', // should be a boolean
            'myTasks' => 'not_a_boolean', // should be a boolean
            'globalTaskSets' => 'not_a_boolean', // should be a boolean
            'globalDashboardTaskSets' => 'not_a_boolean', // should be a boolean
            'nativeTasks' => 'not_a_boolean', // should be a boolean
            'topicID' => 'not_an_integer', // should be an integer
            'perPage' => 'not_an_integer', // should be an integer
            'orderBy' => 'invalid_order', // should be 'asc' or 'desc'
            'orderByField' => 'invalid_field', // should be a valid field name
            'page' => 'not_an_integer', // should be an integer
            'locationID' => 'not_an_integer', // should be an integer
            'dashboardID' => 'not_an_integer', // should be an integer
            'userID' => 'not_an_integer', // should be an integer
            'type' => 'invalid_type', // should be a valid task type
        ];

        $validator = Validator::make($invalidData, (new GetTaskSetRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
