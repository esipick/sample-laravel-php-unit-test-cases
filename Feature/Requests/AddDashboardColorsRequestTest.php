<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\AddDashboardColorsRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AddDashboardColorsRequestTest extends TestCase
{
    public function testAuthorizeAlwaysReturnsTrue()
    {
        $request = new AddDashboardColorsRequest();

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
            'maximumScore' => 100,
            'minimumScore' => 0,
            'colorRanges' => [
                [
                    'from' => 0,
                    'to' => 50,
                    'colorCode' => '#FF0000',
                    'colorLabel' => 'Red',
                ],
                [
                    'from' => 51,
                    'to' => 100,
                    'colorCode' => '#00FF00',
                    'colorLabel' => 'Green',
                ],
            ],
        ];

        $validator = Validator::make($data, (new AddDashboardColorsRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'dashboardID' => 'not_an_integer',
            'maximumScore' => 'not_an_integer',
            'minimumScore' => -1,
            'colorRanges' => [
                [
                    'from' => 'not_an_integer',
                    'to' => 'not_an_integer',
                    'colorCode' => 'invalid_color_code',
                    'colorLabel' => str_repeat('a', 201), // Exceeds the max length
                ],
            ],
        ];

        $validator = Validator::make($invalidData, (new AddDashboardColorsRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
