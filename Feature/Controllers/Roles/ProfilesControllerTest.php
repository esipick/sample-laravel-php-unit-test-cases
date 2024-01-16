<?php

namespace Feature\Controllers\Roles;

use App\Models\Cred;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\ProfileCred;
use App\Models\Security;
use App\Models\User;
use Tests\TestCase;

class ProfilesControllerTest extends TestCase
{
    public function testProfilesIndex()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        Profile::factory()->for($customer)->for($user)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->get('/api/profiles?perPage=1&orderBy=desc&orderByField=created_at');
        $response->assertStatus(200)
            ->assertJsonStructure(['data'])->assertJsonCount(1, 'data.data');
    }

    public function testProfilesLocationIndex()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        Profile::factory()->for($customer)->for($user)->for($location)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->get('/api/profiles?locationID='.$location->locationID);
        $response->assertStatus(200)
            ->assertJsonStructure(['data'])->assertJsonCount(1, 'data.data');
    }

    public function testProfilesLocationHasGlobalRolesIndex()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        Profile::factory()->for($customer)->for($user)->for($location)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->get('/api/profiles?hasGlobalRoles=1&locationID='.$location->locationID);
        $response->assertStatus(200)
            ->assertJsonStructure(['data'])->assertJsonCount(1, 'data.data');
    }

    public function testProfilesOnlyLocationUserGlobalRolesIndex()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->get('/api/profiles?onlyLocationUserGlobalRoles=1&locationID='.$location->locationID);
        $response->assertSuccessful()
            ->assertJsonStructure(['data'])->assertJsonCount(1, 'data.data');
    }

    public function testGetProfilesWithSearch()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create(['profileDescription' => 'example-search-term']);
        Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->get('/api/profiles?search=example-search-term&onlyLocationUserGlobalRoles=1&locationID='.$location->locationID);
        $response->assertStatus(200)
            ->assertJsonStructure(['data'])->assertJsonCount(1, 'data.data');
    }

    public function testProfilesShow()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->get('/api/profiles/'.$profile->profileID);

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function testProfilesStore()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $headers = $this->authenticateUser($user);

        $profileData = [
            'profileDescription' => 'New Profile',
            'locationID' => $location->locationID,
        ];

        $response = $this->withHeaders($headers)
            ->post('/api/profiles', $profileData);

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'message']);
    }

    public function testProfilesUpdate()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->create();
        $creds = Cred::all();
        $creds->each(function ($cred) use ($profile) {
            ProfileCred::factory()->for($profile)->for($cred)->create();
        });
        $headers = $this->authenticateUser($user);
        $profileCred = $profile->profileCreds()->first();
        $updatedData = [
            'profileDescription' => 'Updated Profile Description',
            'profileCredIDs' => [$profileCred->profileCredID],
            'profileCredStatus' => 2,
        ];

        $response = $this->withHeaders($headers)
            ->put('/api/profiles/'.$profile->profileID, $updatedData);

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'message']);
    }

    public function testProfilesDestroy()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)
            ->delete('/api/profiles/'.$profile->profileID);

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'message']);
    }
}
