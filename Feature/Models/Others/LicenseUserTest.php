<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\LicenseIndustry;
use App\Models\LicenseUser;
use App\Models\User;
use Tests\TestCase;

class LicenseUserTest extends TestCase
{
    public function testLicenseUserBelongsToLicenseIndustry()
    {
        $customer = Customer::factory()->create();
        $licenseIndustry = LicenseIndustry::factory()->create();
        $user = User::factory()->for($customer)->create();
        $licenseUser = LicenseUser::factory()->for($licenseIndustry)->for($user)->create();

        $relatedLicenseIndustry = $licenseUser->licenseIndustry;

        $this->assertEquals($licenseIndustry->licenseName, $relatedLicenseIndustry->licenseName);
    }

    public function testLicenseUserBelongsToUser()
    {
        $customer = Customer::factory()->create();
        $licenseIndustry = LicenseIndustry::factory()->create();
        $user = User::factory()->for($customer)->create();
        $licenseUser = LicenseUser::factory()->for($licenseIndustry)->for($user)->create();

        // Retrieve the related User
        $relatedUser = $licenseUser->user;

        // Assert that the related User matches the factory-generated data
        $this->assertEquals($user->name, $relatedUser->name);
    }
}
