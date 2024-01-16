<?php

namespace Feature\Controllers\Others;

use App\Models\Customer;
use App\Models\User;
use Tests\TestCase;

class ValidationControllerTest extends TestCase
{
    public function testCheckUsernameAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $requestData = [
            'userLoginName' => 'testuser',
        ];

        $response = $this->withHeaders($headers)->post('/api/check-username', $requestData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testCheckInvalidUsernameAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->post('/api/check-username');

        $response->assertStatus(500);
    }

    public function testCheckUserEmailAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $requestData = [
            'userEmail' => 'testuser@example.com',
        ];

        $response = $this->withHeaders($headers)->post('/api/check-user-email', $requestData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testCheckInvalidUserEmailAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->post('/api/check-user-email');

        $response->assertStatus(500);
    }
}
