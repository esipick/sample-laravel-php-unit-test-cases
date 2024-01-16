<?php

namespace Feature\Models\Others;

use App\Collections\Role\ProfileCredCollection;
use App\Models\Cred;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\ProfileCred;
use App\Models\User;
use Tests\TestCase;

class ProfileCredTest extends TestCase
{
    public function testBelongsToRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $cred = Cred::factory()->create(['credDescription' => 'sample descrition', 'credToolTip' => 'small tooltip']);
        $profileCred = ProfileCred::factory()->for($profile)->for($cred)->create();

        $this->assertInstanceOf(ProfileCred::class, $profileCred);
        $this->assertInstanceOf(Profile::class, $profileCred->profile);
        $this->assertInstanceOf(Cred::class, $profileCred->cred);
    }

    public function testNewCollection()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $cred = Cred::factory()->create(['credDescription' => 'sample descrition', 'credToolTip' => 'small tooltip']);
        $profileCred = ProfileCred::factory()->for($profile)->for($cred)->create();

        $this->assertInstanceOf(ProfileCredCollection::class, $profileCred->newCollection());
    }
}
