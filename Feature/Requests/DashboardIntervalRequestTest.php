<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\DashboardIntervalRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DashboardIntervalRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = new DashboardIntervalRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $validData = [
            'dashboardID' => $dashboard->dashboardID,
        ];

        $validator = Validator::make($validData, (new DashboardIntervalRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'dashboardID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new DashboardIntervalRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
