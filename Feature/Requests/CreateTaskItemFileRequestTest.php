<?php

namespace Tests\Feature\Requests;

use App\Enum\TaskItemTypeEnum;
use App\Http\Requests\CreateTaskItemFileRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateTaskItemFileRequestTest extends TestCase
{
    public function testAuthorizeAlwaysReturnsTrue()
    {
        $request = new CreateTaskItemFileRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create(
            ['itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value]
        );
        $validData = [
            'taskID' => $task->taskID,
            'itemID' => $taskItem->itemID,
            'fileName' => 'ValidFileName',
            'uploadFileName' => 'ValidUploadFileName',
        ];

        $validator = Validator::make($validData, (new CreateTaskItemFileRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'taskID' => 'not_an_integer',
            'itemID' => 'not_an_integer',
            'fileName' => null, // Missing required field
            'uploadFileName' => '', // Empty field
        ];

        $validator = Validator::make($invalidData, (new CreateTaskItemFileRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
