<?php

namespace Feature\Controllers\Roles;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\User;
use Tests\TestCase;

class SecuritiesControllerTest extends TestCase
{
    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();

        $requestData = [
            'locationID' => $location->locationID,
            'profileID' => $profile->profileID,
            'userID' => $user->userID,
        ];

        $apiURL = '/api/securities';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('security', [
            'locationID' => $location->locationID,
            'profileID' => $profile->profileID,
            'userID' => $user->userID,
        ]);
    }

    public function testStoreFunctionCaseException()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $security = Security::factory()->for($location)->for($profile)->for($user)->create();

        $requestData = [
            'locationID' => $location->locationID,
            'profileID' => $profile->profileID,
            'userID' => $user->userID,
        ];

        $apiURL = '/api/securities';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertServerError();
    }

    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->create();
        $security = Security::factory()->for($location)->for($profile)->for($user)->create();

        $apiURL = '/api/securities/'.$security->securityID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertSoftDeleted('security', ['securityID' => $security->securityID]);
    }
}
