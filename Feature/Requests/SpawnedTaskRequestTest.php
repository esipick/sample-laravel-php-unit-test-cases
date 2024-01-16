<?php

namespace Tests\Feature\Requests;

use App\Enum\TaskItemTypeEnum;
use App\Http\Requests\SpawnedTaskRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardTimeInterval;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SpawnedTaskRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new SpawnedTaskRequest();

        $this->assertTrue($request->authorize());
    }

    public function testValidDataPassesValidation()
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
        $dashboard = Dashboard::factory()->for($customer)->create([
            'isColorTemplateSaved' => 1
        ]);

        $dashboardTimeInterval = DashboardTimeInterval::factory()->for($dashboard)->for($customer)->create([
            'visibleOnDashboard' => 1
        ]);

        $validData = [
            'assignmentType' => 'someType',
            'locationID' => $location->locationID,
            'profileID' => $profile->profileID,
            'taskID' => $task->taskID,
            'itemID' => $taskItem->itemID,
            'batchID' => $task->batchID,
            'timeIntervalID' => $dashboardTimeInterval->timeIntervalID,
            'timestamp' => '2023-11-10 12:00:00',
            'userID' => $user->userID,
            'allDay' => true,
        ];

        $validator = Validator::make($validData, (new SpawnedTaskRequest())->rules());
        $this->assertFalse($validator->fails());
    }

    public function testLocationIDIsRequired()
    {
        $invalidData = [
            'assignmentType' => 'someType',
            'timestamp' => '2023-11-10 12:00:00',
            'allDay' => true,
        ];

        $validator = Validator::make($invalidData, (new SpawnedTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('locationID', $validator->errors()->toArray());
    }

    // Add more test cases for other fields and rules as needed

    public function testTimestampIsRequired()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        $invalidData = [
            'assignmentType' => 'someType',
            'locationID' => $location->locationID,
            'allDay' => true,
        ];

        $validator = Validator::make($invalidData, (new SpawnedTaskRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('timestamp', $validator->errors()->toArray());
    }

}
