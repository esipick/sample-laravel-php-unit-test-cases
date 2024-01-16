<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreProfileRequest;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreProfileRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreProfileRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        $validData = [
            'profileDescription' => 'Valid Profile Description',
            'locationID' => $location->locationID,
        ];

        $validator = Validator::make($validData, (new StoreProfileRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testProfileDescriptionCanBeEmpty()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        $validData = [
            'locationID' => $location->locationID,
        ];

        $validator = Validator::make($validData, (new StoreProfileRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testLocationIDCanBeEmpty()
    {
        $validData = [
            'profileDescription' => 'Valid Profile Description',
        ];

        $validator = Validator::make($validData, (new StoreProfileRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testInvalidLocationIDFailsValidation()
    {
        $invalidData = [
            'profileDescription' => 'Valid Profile Description',
            'locationID' => 999, // Assuming 999 is an invalid location ID
        ];

        $validator = Validator::make($invalidData, (new StoreProfileRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('locationID', $validator->errors()->toArray());
    }

}
