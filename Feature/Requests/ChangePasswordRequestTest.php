<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ChangePasswordRequestTest extends TestCase
{
    public function testAuthorizeAlwaysReturnsTrue()
    {
        $request = new ChangePasswordRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $validData = [
            'currentPassword' => 'ValidCurrentPassword',
            'password' => 'ValidNewPassword',
        ];

        $validator = Validator::make($validData, (new ChangePasswordRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'currentPassword' => null, // Missing required field
            'password' => 'short', // Doesn't meet the minimum length
        ];

        $validator = Validator::make($invalidData, (new ChangePasswordRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
