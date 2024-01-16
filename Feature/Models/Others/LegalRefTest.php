<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\LegalRef;
use Tests\TestCase;

class LegalRefTest extends TestCase
{
    public function testLegalRefBelongsToCustomer()
    {
        $customer = Customer::factory()->create();
        $legalRef = LegalRef::factory()->for($customer)->create();

        $relatedCustomer = $legalRef->customer;

        $this->assertInstanceOf(Customer::class, $relatedCustomer);
        $this->assertEquals($customer->customerID, $relatedCustomer->customerID);
    }

    public function testLegalRefHasDefaultName()
    {
        $customer = Customer::factory()->create();
        $legalRef = LegalRef::factory()->for($customer)->create(['name' => 'New Law Reference']);

        $this->assertEquals('New Law Reference', $legalRef->name);
    }
}
