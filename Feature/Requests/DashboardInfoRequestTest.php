<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\DashboardInfoRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class DashboardInfoRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = new DashboardInfoRequest();

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

        $validData = [
            'dashboardID' => $dashboard->dashboardID,
        ];

        $validator = Validator::make($validData, (new DashboardInfoRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'dashboardID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new DashboardInfoRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
