<?php

namespace Feature\Models\Others;

use App\Models\Cred;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\ProfileCred;
use App\Models\User;
use Tests\TestCase;

class CredTest extends TestCase
{
    public function testProfileCredsRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $cred = Cred::factory()->create();
        $profileCred1 = ProfileCred::factory()->for($profile)->for($cred)->create();
        $profileCred2 = ProfileCred::factory()->for($profile)->for($cred)->create();
        $retrievedProfileCreds = $cred->profileCreds;

        $this->assertContains($profileCred1->credID, $retrievedProfileCreds->pluck('credID'));
        $this->assertContains($profileCred2->credID, $retrievedProfileCreds->pluck('credID'));

    }
}
