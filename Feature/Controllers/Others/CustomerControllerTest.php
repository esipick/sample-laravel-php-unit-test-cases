<?php

namespace Feature\Controllers\Others;

use App\Enum\SocialiteProviderType;
use App\Models\Customer;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    public function testGetCustomerInfoWithDefaultCustomer()
    {
        config(['services.defaults.default_customer_id' => 1]);
        $origin = 'example.com';

        $customer = Customer::factory()->create([
            'customerName' => 'Test Customer',
            'domain' => $origin,
        ]);

        $azureClient = $customer->socialiteClient()->create([
            'type' => SocialiteProviderType::AZURE,
            'clientID' => 'azure-client-id',
        ]);
        $oktaClient = $customer->socialiteClient()->create([
            'type' => SocialiteProviderType::OKTA,
            'clientID' => 'okta-client-id',
        ]);

        $headers = ['Referer' => 'https://'.$origin.'/#!'];

        $response = $this->get('/api/customer', $headers);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);

        $response->assertJson(['data' => [
            'domain' => 'https://'.$origin.'/#!',
            'displayAzureLogin' => true,
            'displayOktaLogin' => true,
            'name' => 'Test Customer',
        ]]);
    }

    public function testGetCustomerInfoWithDefaultCustomerAndMissingClients()
    {
        config(['services.defaults.default_customer_id' => 1]);
        $origin = 'example.com';

        $customer = Customer::factory()->create([
            'customerName' => 'Test Customer',
            'domain' => $origin,
        ]);

        $headers = ['Referer' => 'https://'.$origin.'/#!'];

        $response = $this->get('/api/customer', $headers);

        $response->assertStatus(200);

        $response->assertJson(['data' => [
            'domain' => 'https://'.$origin.'/#!',
            'displayAzureLogin' => false,
            'displayOktaLogin' => false,
            'name' => 'Test Customer',
        ]]);
    }
}
