<?php

namespace Feature\Controllers\Others;

use App\Models\Customer;
use App\Models\LicenseIndustry;
use App\Models\LicenseUser;
use App\Models\User;
use Tests\TestCase;

class LicenseUserControllerTest extends TestCase
{
    public function testLicenseUserStoreAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $licenseIndustry = LicenseIndustry::factory()->create();

        $requestData = [
            'userID' => $user->userID,
            'licenseID' => $licenseIndustry->licenseID,
        ];

        $response = $this->withHeaders($headers)->post('/api/license-user', $requestData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testLicenseUserUpdateAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $licenseIndustry = LicenseIndustry::factory()->create();
        $licenseUser = LicenseUser::factory()->for($user)->for($licenseIndustry)->create();

        $updateData = [
            'userID' => $user2->userID,
        ];

        $response = $this->withHeaders($headers)->put("/api/license-user/{$licenseUser->license_userID}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testDestroyAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $licenseIndustry = LicenseIndustry::factory()->create();
        $licenseUser = LicenseUser::factory()->for($user)->for($licenseIndustry)->create();

        $response = $this->withHeaders($headers)->delete("/api/license-user/{$licenseUser->license_userID}");

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
    }
}
