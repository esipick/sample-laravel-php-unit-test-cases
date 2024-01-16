<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\RecallDeployedTaskRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RecallDeployedTaskRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new RecallDeployedTaskRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $validData = [
            'taskID' => $task->taskID,
            'locationID' => $location->locationID,
            'openTasksOption' => true,
        ];

        $validator = Validator::make($validData, (new RecallDeployedTaskRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testTaskIDIsRequired()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $invalidData = [
            'locationID' => $location->locationID,
            'openTasksOption' => true,
        ];

        $validator = Validator::make($invalidData, (new RecallDeployedTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('taskID', $validator->errors()->toArray());
    }

    public function testLocationIDIsRequired()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $invalidData = [
            'taskID' => $task->taskID,
            'openTasksOption' => true,
        ];

        $validator = Validator::make($invalidData, (new RecallDeployedTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('locationID', $validator->errors()->toArray());
    }

    public function testOpenTasksOptionIsRequired()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $invalidData = [
            'taskID' => $task->taskID,
            'locationID' => $location->locationID,
        ];

        $validator = Validator::make($invalidData, (new RecallDeployedTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('openTasksOption', $validator->errors()->toArray());
    }

    public function testTaskIDMustBeInteger()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $invalidData = [
            'taskID' => 'not_an_integer',
            'locationID' => $location->locationID,
            'openTasksOption' => true,
        ];

        $validator = Validator::make($invalidData, (new RecallDeployedTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('taskID', $validator->errors()->toArray());
    }

    public function testLocationIDMustBeInteger()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $invalidData = [
            'taskID' => $task->taskID,
            'locationID' => 'not_an_integer',
            'openTasksOption' => true,
        ];

        $validator = Validator::make($invalidData, (new RecallDeployedTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('locationID', $validator->errors()->toArray());
    }

    public function testOpenTasksOptionMustBeBoolean()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $invalidData = [
            'taskID' => $task->taskID,
            'locationID' => $location->locationID,
            'openTasksOption' => 'not_a_boolean',
        ];

        $validator = Validator::make($invalidData, (new RecallDeployedTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('openTasksOption', $validator->errors()->toArray());
    }
}
