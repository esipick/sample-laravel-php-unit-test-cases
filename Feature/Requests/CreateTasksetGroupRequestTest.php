<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\CreateTasksetGroupRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategory;
use App\Models\DashboardTaskSetCategory;
use App\Models\DashboardTaskSetsItem;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateTasksetGroupRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = new CreateTasksetGroupRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $dashboard = Dashboard::factory()->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();

        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->create();

        $validData = [
            'dashboardItemID' => $dashboardTaskSetsItem->dashboardItemID,
            'taskSetID' => $task->taskID,
        ];

        $validator = Validator::make($validData, (new CreateTasksetGroupRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'dashboardItemID' => 'not_an_integer',
            'taskSetID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new CreateTasksetGroupRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
