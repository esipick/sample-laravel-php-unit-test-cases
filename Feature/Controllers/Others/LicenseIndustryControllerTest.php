<?php

namespace Feature\Controllers\Others;

use App\Models\Customer;
use App\Models\LicenseIndustry;
use App\Models\User;
use Tests\TestCase;

class LicenseIndustryControllerTest extends TestCase
{
    public function testLicenseIndustryIndexAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        LicenseIndustry::factory()->count(3)->create();

        $response = $this->withHeaders($headers)->get('/api/licenses');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
        $response->assertJsonCount(5, 'data');
    }
}
