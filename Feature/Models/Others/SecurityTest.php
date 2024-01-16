<?php

namespace Feature\Models\Others;

use App\Collections\Role\SecurityCollection;
use App\Models\Cred;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\ProfileCred;
use App\Models\Security;
use App\Models\User;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    public function testBelongsToRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $security = Security::factory()->for($profile)->for($user)->for($location)->create();

        $this->assertInstanceOf(Security::class, $security);
        $this->assertInstanceOf(Profile::class, $security->profile);
        $this->assertInstanceOf(User::class, $security->user);
        $this->assertInstanceOf(Location::class, $security->location);
    }

    public function testHasManyRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $security = Security::factory()->for($profile)->for($user)->for($location)->create();

        $cred = Cred::factory()->create(['credDescription' => 'sample descrition', 'credToolTip' => 'small tooltip']);
        ProfileCred::factory()->for($profile)->for($cred)->create();
        $this->assertInstanceOf(ProfileCred::class, $security->profileCreds[0]);

        $this->assertInstanceOf(Profile::class, $security->profiles[0]);
    }

    public function testNewCollection()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $security = Security::factory()->for($profile)->for($user)->for($location)->create();

        $this->assertInstanceOf(SecurityCollection::class, $security->newCollection());
    }
}
