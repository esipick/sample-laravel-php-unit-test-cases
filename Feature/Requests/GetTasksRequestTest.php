<?php

namespace Tests\Feature\Requests;

use App\Enum\TaskType;
use App\Http\Requests\GetTasksRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTasksRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrue()
    {
        $request = new GetTasksRequest();
        $this->assertTrue($request->authorize());
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
            'search' => 'test',
            'openTasks' => true,
            'spawnedTasks' => false,
            'deployed' => true,
            'nativeTasks' => false,
            'myTasks' => true,
            'globalTasks' => false,
            'globalDashboardTasks' => true,
            'topicID' => $topic->topicID,
            'perPage' => 10,
            'orderBy' => 'asc',
            'orderByField' => 'locationID',
            'page' => 1,
            'locationID' => $location->locationID,
            'userID' => $user->userID,
            'type' => TaskType::ASSESSMENT->value,
            'startDate' => '2023-01-01',
            'endDate' => '2023-01-31',
        ];

        $validator = Validator::make($validData, (new GetTasksRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'search' => 123, // should be a string
            'openTasks' => 'not_a_boolean', // should be a boolean
            'spawnedTasks' => 'not_a_boolean', // should be a boolean
            'deployed' => 'not_a_boolean', // should be a boolean
            'nativeTasks' => 'not_a_boolean', // should be a boolean
            'myTasks' => 'not_a_boolean', // should be a boolean
            'globalTasks' => 'not_a_boolean', // should be a boolean
            'globalDashboardTasks' => 'not_a_boolean', // should be a boolean
            'topicID' => 'not_an_integer', // should be an integer
            'perPage' => 'not_an_integer', // should be an integer
            'orderBy' => 'invalid_order', // should be 'asc' or 'desc'
            'orderByField' => 'invalid_field', // should be a valid field name
            'page' => 'not_an_integer', // should be an integer
            'locationID' => 'not_an_integer', // should be an integer
            'userID' => 'not_an_integer', // should be an integer
            'type' => 'invalid_type', // should be a valid task type
            'startDate' => 'invalid_date_format', // should be in Y-m-d format
            'endDate' => 'invalid_date_format', // should be in Y-m-d format
        ];

        $validator = Validator::make($invalidData, (new GetTasksRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
