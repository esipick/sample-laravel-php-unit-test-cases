<?php

namespace Feature\Models\Others;

use App\Collections\Role\ProfileCollection;
use App\Models\Cred;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\ProfileCred;
use App\Models\Security;
use App\Models\User;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function testBelongsToRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $this->assertInstanceOf(Profile::class, $profile);
        $this->assertInstanceOf(Customer::class, $profile->customer);
        $this->assertInstanceOf(User::class, $profile->user);
        $this->assertInstanceOf(Location::class, $profile->location);
    }

    public function testHasManyRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        Security::factory()->for($user)->for($location)->for($profile)->create();
        $this->assertInstanceOf(Security::class, $profile->securities[0]);

        $cred = Cred::factory()->create(['credDescription' => 'sample descrition', 'credToolTip' => 'small tooltip']);
        ProfileCred::factory()->for($profile)->for($cred)->create();
        $this->assertInstanceOf(ProfileCred::class, $profile->profileCreds[0]);
    }

    public function testBelongsToManyRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $cred1 = Cred::factory()->create(['credDescription' => 'sample descrition 1', 'credToolTip' => 'small tooltip 1']);
        $cred2 = Cred::factory()->create(['credDescription' => 'sample descrition 2', 'credToolTip' => 'small tooltip 2']);

        $profile->creds()->attach([$cred1->credID, $cred2->credID]);

        $associatedCreds = $profile->creds;

        $this->assertTrue($associatedCreds->contains($cred1));
        $this->assertTrue($associatedCreds->contains($cred2));
    }

    public function testNewCollectionAndGetterMethods()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        $this->assertInstanceOf(ProfileCollection::class, $profile->newCollection());
        $this->assertTrue($profile->isLocalProfile);
        $this->assertFalse($profile->isGlobalProfile);
    }
}
