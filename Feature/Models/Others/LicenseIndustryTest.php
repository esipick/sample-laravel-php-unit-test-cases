<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\LicenseIndustry;
use App\Models\LicenseUser;
use App\Models\User;
use Tests\TestCase;

class LicenseIndustryTest extends TestCase
{
    public function testLicenseIndustryCanBeCreated()
    {
        $licenseIndustry = LicenseIndustry::create([
            'licenseName' => 'Example License',
            'licenseDescription' => 'This is an example license description',
        ]);
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $licenseIndustry = LicenseIndustry::find($licenseIndustry->licenseID);
        LicenseUser::factory()->for($licenseIndustry)->for($user)->count(5)->create();

        $this->assertEquals('Example License', $licenseIndustry->licenseName);
        $this->assertEquals('This is an example license description', $licenseIndustry->licenseDescription);

        // Retrieve the related LicenseUsers via the relationship
        $relatedLicenseUsers = $licenseIndustry->licenceUsers;

        // Assert that there are two related LicenseUser records
        $this->assertCount(5, $relatedLicenseUsers);
    }
}
