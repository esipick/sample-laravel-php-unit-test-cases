<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\LoginLog;
use App\Models\User;
use Tests\TestCase;

class LoginLogTest extends TestCase
{
    public function testCanCreateLoginLog()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $insertArray = [
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Chrome',
            'userID' => $user->userID,
        ];

        $loginLog = LoginLog::create($insertArray);

        $this->assertInstanceOf(loginLog::class, $loginLog);
        $this->assertEquals($insertArray['ip_address'], $loginLog->ip_address);
        $this->assertEquals($insertArray['user_agent'], $loginLog->user_agent);
        $this->assertEquals($insertArray['userID'], $loginLog->userID);
    }

    public function testCanUpdateLoginLog()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $insertArray = [
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Chrome',
            'userID' => $user->userID,
        ];

        $loginLog = LoginLog::create($insertArray);

        $updateArray = [
            'ip_address' => '192.168.1.2',
            'user_agent' => 'Firefox',
        ];

        $loginLog->update($updateArray);

        $this->assertInstanceOf(loginLog::class, $loginLog);
        $this->assertEquals($updateArray['ip_address'], $loginLog->ip_address);
        $this->assertEquals($updateArray['user_agent'], $loginLog->user_agent);
    }
}
