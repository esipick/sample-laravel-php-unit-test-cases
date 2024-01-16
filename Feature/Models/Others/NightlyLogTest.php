<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\NightlyLog;
use Tests\TestCase;

class NightlyLogTest extends TestCase
{
    public function testCanCreateLoginLog()
    {
        $customer = Customer::factory()->create();

        $insertArray = [
            'nightlyLogNotes' => 'insert sample log notes',
            'nightlyLogDate' => rand(1000, 9999),
            'nightlyLogSuccess' => rand(1000, 9999),
            'customerID' => $customer->customerID,
        ];

        $nightlyLog = NightlyLog::create($insertArray);

        $this->assertInstanceOf(NightlyLog::class, $nightlyLog);
        $this->assertEquals($insertArray['nightlyLogNotes'], $nightlyLog->nightlyLogNotes);
        $this->assertEquals($insertArray['nightlyLogDate'], $nightlyLog->nightlyLogDate);
        $this->assertEquals($insertArray['nightlyLogSuccess'], $nightlyLog->nightlyLogSuccess);
        $this->assertEquals($insertArray['customerID'], $nightlyLog->customerID);
    }
}
