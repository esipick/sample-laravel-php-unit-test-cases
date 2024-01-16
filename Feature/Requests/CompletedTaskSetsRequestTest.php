<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\CompletedTaskSetsRequest;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CompletedTaskSetsRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = new CompletedTaskSetsRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {

        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        $validData = [
            'locationID' => $location->locationID,
        ];

        $validator = Validator::make($validData, (new CompletedTaskSetsRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'locationID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new CompletedTaskSetsRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
