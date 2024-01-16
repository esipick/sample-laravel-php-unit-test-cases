<?php

namespace Feature\Models\Others;

use App\Models\PasswordReset;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function testCanCreatePasswordReset()
    {
        $insertArray = [
            'userEmail' => 'test@email.com',
            'token' => sha1(time()),
            'created_at' => now(),
        ];

        $passwordReset = PasswordReset::create($insertArray);

        $this->assertInstanceOf(PasswordReset::class, $passwordReset);
        $this->assertEquals($insertArray['userEmail'], $passwordReset->userEmail);
        $this->assertEquals($insertArray['token'], $passwordReset->token);
        $this->assertEquals($insertArray['created_at'], $passwordReset->created_at);
    }
}
