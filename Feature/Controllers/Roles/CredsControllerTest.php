<?php

namespace Tests\Feature\Controllers;

use App\Models\Cred;
use App\Models\Customer;
use App\Models\User;
use Tests\TestCase;

class CredsControllerTest extends TestCase
{
    public function testIndexFunctionCaseType()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $cred = Cred::factory()->times(rand(1, 10))->create();

        $apiURL = '/api/creds';
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $cred->pluck('credID')->toArray();
        $responseArray = collect($response->json('data'))->pluck('credID')->toArray();

        sort($requestArray);
        sort($responseArray);

        // $this->assertEquals($requestArray, $responseArray);
    }
}
