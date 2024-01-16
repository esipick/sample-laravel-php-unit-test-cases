<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ResetPasswordRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new ResetPasswordRequest();

        $this->assertTrue($request->authorize());
    }

    public function testValidDataPassesValidation()
    {
        $validData = [
            'password' => 'valid_password',
            'token' => 'valid_token',
        ];

        $validator = Validator::make($validData, (new ResetPasswordRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testPasswordIsRequired()
    {
        $invalidData = [
            'token' => 'valid_token',
        ];

        $validator = Validator::make($invalidData, (new ResetPasswordRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function testPasswordMustHaveMinimumLength()
    {
        $invalidData = [
            'password' => 'short',
            'token' => 'valid_token',
        ];

        $validator = Validator::make($invalidData, (new ResetPasswordRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function testTokenIsRequired()
    {
        $invalidData = [
            'password' => 'valid_password',
        ];

        $validator = Validator::make($invalidData, (new ResetPasswordRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('token', $validator->errors()->toArray());
    }

}
