<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetLocationsRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardTaskSetsItem;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetLocationsRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthorizeAlwaysReturnsTrue()
    {
        $request = new GetLocationsRequest();

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
        DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->count(5)->create();

        $validData = [
            'dashboardID' => $dashboard->dashboardID,
            'search' => 'example',
            'assignedLocations' => true,
            'perPage' => 10,
            'orderBy' => 'asc',
            'orderByField' => 'locationID',
            'page' => 1,
        ];

        $validator = Validator::make($validData, (new GetLocationsRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'dashboardID' => 'not_an_integer',
            'search' => 123,
            'assignedLocations' => 'not_a_boolean',
            'perPage' => 'not_an_integer',
            'orderBy' => 'invalid_order',
            'orderByField' => 'invalid_field',
            'page' => 0,
        ];

        $validator = Validator::make($invalidData, (new GetLocationsRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
