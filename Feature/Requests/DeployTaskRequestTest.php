<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\DeployTaskRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DeployTaskRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = new DeployTaskRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $location1 = Location::factory()->for($customer)->create();
        $location2 = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $validData = [
            'taskID' => $task->taskID,
            'locationIDS' => [$location1->locationID, $location2->locationID],
            'assignmentOption' => true,
            'notificationOption' => false,
            'openTasksOption' => true,
            'scheduleOption1' => false,
            'scheduleOption2' => true,
        ];

        $validator = Validator::make($validData, (new DeployTaskRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'taskID' => 'not_an_integer',
            'locationIDS' => 'not_an_array',
            'assignmentOption' => 'not_a_boolean',
            'notificationOption' => 'not_a_boolean',
            'openTasksOption' => 'not_a_boolean',
            'scheduleOption1' => 'not_a_boolean',
            'scheduleOption2' => 'not_a_boolean',
        ];

        $validator = Validator::make($invalidData, (new DeployTaskRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
