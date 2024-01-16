<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\AssociateTaskSetRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardTaskSetsItem;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\User;
use App\Rules\AlreadyDeployed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AssociateTaskSetRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = new AssociateTaskSetRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $validData = [
            'dashboardID' => $dashboard->dashboardID,
            'taskSetID' => $task->taskID,
        ];

        $validator = Validator::make($validData, (new AssociateTaskSetRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'dashboardID' => 'not_an_integer',
            'taskSetID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new AssociateTaskSetRequest())->rules());

        $this->assertTrue($validator->fails());
    }

    public function testRulesValidationFailsWithAlreadyDeployedTaskSet()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->count(5)->create();
        $invalidData = [
            'dashboardID' => $dashboard->dashboardID,
            'taskSetID' => $task->taskID,
        ];

        $validator = Validator::make($invalidData, (new AssociateTaskSetRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
