<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\AssessmentDashboardRequest;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AssessmentDashboardRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = new AssessmentDashboardRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        $validData = [
            'locationID' => $location->locationID,
            'dashboardName' => 'Valid Dashboard Name',
        ];

        $validator = Validator::make($validData, (new AssessmentDashboardRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationPassesWithMissingDashboardName()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $validDataWithoutDashboardName = [
            'locationID' => $location->locationID,
        ];

        $validator = Validator::make($validDataWithoutDashboardName, (new AssessmentDashboardRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'locationID' => 'not_an_integer',
            'dashboardName' => str_repeat('a', 91), // Exceeds the max length
        ];

        $validator = Validator::make($invalidData, (new AssessmentDashboardRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
