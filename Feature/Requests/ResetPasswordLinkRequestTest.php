<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\ResetPasswordLinkRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ResetPasswordLinkRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new ResetPasswordLinkRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $validData = [
            'email' => $user->userEmail,
        ];

        $validator = Validator::make($validData, (new ResetPasswordLinkRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testEmailIsRequired()
    {
        $invalidData = [];

        $validator = Validator::make($invalidData, (new ResetPasswordLinkRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function testEmailMustBeValid()
    {
        $invalidData = [
            'email' => 'invalid_email',
        ];

        $validator = Validator::make($invalidData, (new ResetPasswordLinkRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }
}
