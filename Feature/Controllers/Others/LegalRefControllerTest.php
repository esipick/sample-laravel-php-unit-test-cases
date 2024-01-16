<?php

namespace Feature\Controllers\Others;

use App\Models\Customer;
use App\Models\LegalRef;
use App\Models\User;
use Tests\TestCase;

class LegalRefControllerTest extends TestCase
{
    public function testIndexLegalRefAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        LegalRef::factory()->for($customer)->create();

        $response = $this->withHeaders($headers)->get('/api/legal-ref');
        $response->assertStatus(200);

        $response->assertJsonStructure(['data']);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testIndexLegalRefSearchAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        LegalRef::factory()->for($customer)->create(['name' => 'test']);

        $response = $this->withHeaders($headers)->get('/api/legal-ref?search=test&perPage=5&orderByField=created_at&orderBy=desc');
        $response->assertStatus(200);

        $response->assertJsonStructure(['data']);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testStoreLegalRefAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $requestData = [
            'name' => 'New Law',
        ];

        $response = $this->withHeaders($headers)->post('/api/legal-ref', $requestData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
        $this->assertDatabaseHas('legal_refs', ['name' => 'New Law']);
    }

    public function testUpdateLegalRefAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $legalRef = LegalRef::factory()->for($customer)->create();

        $updateData = [
            'name' => 'Updated Law',
        ];

        $response = $this->withHeaders($headers)->put("/api/legal-ref/{$legalRef->legalRefID}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
        $this->assertDatabaseHas('legal_refs', ['name' => 'Updated Law']);
    }

    public function testDestroyLegalRefAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $legalRef = LegalRef::factory()->for($customer)->create();

        $response = $this->withHeaders($headers)->delete("/api/legal-ref/{$legalRef->legalRefID}");

        $response->assertStatus(200);
    }
}
