<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetTimeIntervalInfoRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardTaskSetsItem;
use App\Models\DashboardTaskSetTimeInterval;
use App\Models\DashboardTimeInterval;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTimeIntervalInfoRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrue()
    {
        $request = new GetTimeIntervalInfoRequest();
        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create([
            "dueAt" => null
        ]);

        $dashboard = Dashboard::factory()->for($customer)->create([
            'isColorTemplateSaved' => 1
        ]);

        $dashboardTimeInterval = DashboardTimeInterval::factory()->for($dashboard)->for($customer)->create([
            'visibleOnDashboard' => 1
        ]);

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($user)->for($task)->for($dashboard)->create([
            'status' => 'Active'
        ]);

        $dashboardTaskSetTimeInterval = DashboardTaskSetTimeInterval::factory()->for($task)->for($dashboardTaskSetsItem)->create([
            'status' => 'Active',
            'taskSetID' => $dashboardTaskSetsItem->taskSetID,
            'timeIntervalID' => $dashboardTimeInterval->timeIntervalID
        ]);


        $validData = [
            'timeIntervalID' => $dashboardTaskSetTimeInterval->timeIntervalID,
        ];

        $validator = Validator::make($validData, (new GetTimeIntervalInfoRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'timeIntervalID' => 'not_an_integer', // should be an integer
        ];

        $validator = Validator::make($invalidData, (new GetTimeIntervalInfoRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
