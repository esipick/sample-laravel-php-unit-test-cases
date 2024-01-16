<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\SocialiteClient;
use Tests\TestCase;

class SocialiteClientTest extends TestCase
{
    public function testBelongsToRelationship()
    {
        $customer = Customer::factory()->create();
        $socialiteClient = SocialiteClient::factory()->for($customer)->create();

        $this->assertInstanceOf(SocialiteClient::class, $socialiteClient);
        $this->assertInstanceOf(Customer::class, $socialiteClient->customer);
    }
}
