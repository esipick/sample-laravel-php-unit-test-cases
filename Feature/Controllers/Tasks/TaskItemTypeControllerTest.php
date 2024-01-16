<?php

namespace Feature\Controllers\Tasks;

use App\Models\Customer;
use App\Models\User;
use Tests\TestCase;

class TaskItemTypeControllerTest extends TestCase
{
    /** @test */
    public function testIndexFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->get('/api/task-item-types');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }
}
