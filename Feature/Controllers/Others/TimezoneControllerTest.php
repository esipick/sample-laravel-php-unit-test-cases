<?php

namespace Feature\Controllers\Others;

use App\Models\Customer;
use App\Models\Timezone;
use App\Models\User;
use Tests\TestCase;

class TimezoneControllerTest extends TestCase
{
    public function testTimezoneIndexAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $timezone = Timezone::factory()->create([
            'name' => 'Test Timezone',
            'gmtOffset' => 'UTC+2',
        ]);

        $response = $this->withHeaders($headers)->get('/api/timezone');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data',
        ]);
    }
}
