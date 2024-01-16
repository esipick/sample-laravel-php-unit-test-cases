<?php

namespace Feature\Controllers\Tasks;

use App\Models\Customer;
use App\Models\User;
use Tests\TestCase;

class TaskReoccurTypeControllerTest extends TestCase
{
    public function testIndexFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $apiURL = 'api/task-reoccur-type';
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);
    }
}
