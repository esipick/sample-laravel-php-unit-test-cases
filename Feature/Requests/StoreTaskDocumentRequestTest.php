<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreTaskDocumentRequest;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTaskDocumentRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreTaskDocumentRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $user = User::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $document = Document::factory()->for($customer)->for($location)->create();

        $validData = [
            'taskID' => $task->taskID,
            'documentID' => $document->documentID,
        ];

        $validator = Validator::make($validData, (new StoreTaskDocumentRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRequiredFieldsAreChecked()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $document = Document::factory()->for($customer)->for($location)->create();

        $invalidData = [
            // Missing required 'taskID' field
            'documentID' => $document->documentID,
        ];

        $validator = Validator::make($invalidData, (new StoreTaskDocumentRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('taskID', $validator->errors()->toArray());
    }

    public function testInvalidDataFailsValidation()
    {
        $invalidData = [
            'taskID' => 'invalid', // 'taskID' should be an integer
            'documentID' => 'invalid', // 'documentID' should be an integer
        ];

        $validator = Validator::make($invalidData, (new StoreTaskDocumentRequest())->rules());

        $this->assertTrue($validator->fails());
    }

    public function testNonExistingTaskFailsValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $document = Document::factory()->for($customer)->for($location)->create();

        $nonExistingData = [
            'taskID' => 999, // Assuming this task ID does not exist
            'documentID' => $document->documentID,
        ];

        $validator = Validator::make($nonExistingData, (new StoreTaskDocumentRequest())->rules());

        $this->assertTrue($validator->fails());
    }

    public function testNonExistingDocumentFailsValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $nonExistingData = [
            'taskID' => $task->taskID,
            'documentID' => 999, // Assuming this document ID does not exist
        ];

        $validator = Validator::make($nonExistingData, (new StoreTaskDocumentRequest())->rules());

        $this->assertTrue($validator->fails());
    }

}
