<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\AddColorRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AddColorRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(true);
        $request = new AddColorRequest();
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

        $data = [
            'dashboardID' => $dashboard->dashboardID,
            'from' => 10,
            'to' => 20,
            'colorCode' => '#FF0000',
            'colorLabel' => 'Red',
        ];

        $validator = Validator::make($data, (new AddColorRequest())->rules());

        $this->assertFalse($validator->fails());
    }
    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'dashboardID' => 'not_an_integer',
            'from' => 'not_an_integer',
            'to' => 'not_an_integer',
            'colorCode' => 'invalid_color_code',
            'colorLabel' => str_repeat('a', 201),
        ];

        $validator = Validator::make($invalidData, (new AddColorRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
