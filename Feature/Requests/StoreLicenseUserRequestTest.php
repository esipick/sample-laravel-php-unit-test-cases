<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreLicenseUserRequest;
use App\Models\Customer;
use App\Models\LicenseIndustry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreLicenseUserRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreLicenseUserRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $licenseIndustry = LicenseIndustry::factory()->create();
        $validData = [
            'userID' => $user->userID,
            'licenseID' => $licenseIndustry->licenseID,
            'licenseNumber' => 'ABC123',
        ];

        $validator = Validator::make($validData, (new StoreLicenseUserRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testUserIDIsRequired()
    {
        $licenseIndustry = LicenseIndustry::factory()->create();
        $invalidData = [
            'licenseID' => $licenseIndustry->licenseID,
            'licenseNumber' => 'ABC123',
        ];

        $validator = Validator::make($invalidData, (new StoreLicenseUserRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('userID', $validator->errors()->toArray());
    }

    public function testLicenseIDIsRequired()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $invalidData = [
            'userID' => $user->userID,
            'licenseNumber' => 'ABC123',
        ];

        $validator = Validator::make($invalidData, (new StoreLicenseUserRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('licenseID', $validator->errors()->toArray());
    }
}
