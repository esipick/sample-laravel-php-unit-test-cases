<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreTaskCommentRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTaskCommentRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreTaskCommentRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $validData = [
            'taskID' => $task->taskID,
            'text' => 'This is a valid comment.',
        ];

        $validator = Validator::make($validData, (new StoreTaskCommentRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRequiredFieldsAreChecked()
    {
        $invalidData = [
            // Missing required 'taskID' field
            'text' => 'This is a valid comment.',
        ];

        $validator = Validator::make($invalidData, (new StoreTaskCommentRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('taskID', $validator->errors()->toArray());
    }

    public function testInvalidDataFailsValidation()
    {
        $invalidData = [
            'taskID' => 'invalid', // 'taskID' should be an integer
            'text' => 'This is a valid comment.',
        ];

        $validator = Validator::make($invalidData, (new StoreTaskCommentRequest())->rules());

        $this->assertTrue($validator->fails());
        // Add assertions for other fields as needed
    }

    public function testNonExistingTaskFailsValidation()
    {
        $nonExistingData = [
            'taskID' => 999, // Assuming this task ID does not exist
            'text' => 'This is a valid comment.',
        ];

        $validator = Validator::make($nonExistingData, (new StoreTaskCommentRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
