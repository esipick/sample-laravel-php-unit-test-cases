<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreTaskDelayRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTaskDelayRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreTaskDelayRequest();

        $this->assertTrue($request->authorize());
    }

    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $validData = [
            'reason' => 'Valid reason for delay.',
            'taskID' => $task->taskID,
            'delayEndAt' => now()->addDay()->format('Y-m-d H:i:s'), // Assuming a valid date format
        ];

        $validator = Validator::make($validData, (new StoreTaskDelayRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRequiredFieldsAreChecked()
    {
        $invalidData = [
            // Missing required 'taskID' field
            'reason' => 'Valid reason for delay.',
            'delayEndAt' => now()->addDay(), // Assuming a valid date format
        ];

        $validator = Validator::make($invalidData, (new StoreTaskDelayRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('taskID', $validator->errors()->toArray());
    }

    public function testInvalidDataFailsValidation()
    {
        $invalidData = [
            'reason' => 'Valid reason for delay.',
            'taskID' => 'invalid', // 'taskID' should be an integer
            'delayEndAt' => 'invalid_date_format', // Invalid date format
        ];

        $validator = Validator::make($invalidData, (new StoreTaskDelayRequest())->rules());

        $this->assertTrue($validator->fails());
        // Add assertions for other fields as needed
    }

    public function testNonExistingTaskFailsValidation()
    {
        $nonExistingData = [
            'reason' => 'Valid reason for delay.',
            'taskID' => 999, // Assuming this task ID does not exist
            'delayEndAt' => now()->addDay(), // Assuming a valid date format
        ];

        $validator = Validator::make($nonExistingData, (new StoreTaskDelayRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
