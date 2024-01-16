<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreSecurityRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreSecurityRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreSecurityRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {

        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $validData = [
            'locationID' => $location->locationID,
            'profileID' => $profile->profileID,
            'userID' => $user->userID,
        ];

        $validator = Validator::make($validData, (new StoreSecurityRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRequiredFieldsAreChecked()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $invalidData = [
            // Missing required 'locationID' field
            'profileID' => $profile->profileID,
            'userID' => $user->userID,
        ];

        $validator = Validator::make($invalidData, (new StoreSecurityRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('locationID', $validator->errors()->toArray());
    }

    public function testInvalidDataFailsValidation()
    {
        $invalidData = [
            'locationID' => 'invalid',
            'profileID' => 'invalid',
            'userID' => 'invalid',
        ];

        $validator = Validator::make($invalidData, (new StoreSecurityRequest())->rules());

        $this->assertTrue($validator->fails());
        // Add assertions for other fields as needed
    }

    public function testNonExistingDataFailsValidation()
    {
        $nonExistingData = [
            'locationID' => 999,
            'profileID' => 999,
            'userID' => 999,
        ];

        $validator = Validator::make($nonExistingData, (new StoreSecurityRequest())->rules());

        $this->assertTrue($validator->fails());
    }

}
