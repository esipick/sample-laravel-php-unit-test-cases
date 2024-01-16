<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\Timezone;
use App\Models\User;
use Tests\TestCase;

class LocationTest extends TestCase
{
    public function testLocationBelongsToCustomer()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $relatedCustomer = $location->customer;
        $this->assertEquals($customer->customerName, $relatedCustomer->customerName);
    }

    public function testLocationHasManySecurities()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();

        Security::factory()->for($user)->for($location)->for($profile)->create();

        $relatedSecurities = $location->securities;

        $this->assertCount(1, $relatedSecurities);
    }

    public function testLocationBelongsToTaskBoardUser()
    {
        $customer = Customer::factory()->create();
        $taskBoardUser = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create([
            'locationDefaultUser' => $taskBoardUser->userID,
        ]);

        $relatedTaskBoardUser = $location->taskBoardUser;
        $this->assertEquals($taskBoardUser->userName, $relatedTaskBoardUser->userName);
    }

    public function testLocationHasManyTasks()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        Task::factory()->for($customer)->for($location)->count(5)->create();

        $relatedTasks = $location->tasks;
        $this->assertCount(5, $relatedTasks);
    }

    public function testLocationHasManyDashboards()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $relatedDashboards = $location->dashboards;

        $this->assertCount(1, $relatedDashboards);
    }

    public function testScopeCustomerInfo()
    {
        $customer = Customer::factory()->create();
        $apiUser = User::factory()->for($customer)->create();

        $this->actingAs($apiUser, 'api');

        $locationForCustomer = Location::factory()->for($customer)->create();

        $locations = Location::customerInfo()->get();

        $this->assertEquals($locationForCustomer->locationID, $locations[0]->locationID);
        $this->assertCount(1, $locations);
    }

    public function testScopeActive()
    {
        $customer = Customer::factory()->create();
        $activeLocation = Location::factory()->for($customer)->create([
            'locationDeleted' => 0,
        ]);

        $activeLocations = Location::active()->get();

        $this->assertGreaterThanOrEqual(1, $activeLocations->count());
        $this->assertContains($activeLocation->locationID, $activeLocations->pluck('locationID'));
    }

    public function testGetTimezoneWithRelatedTimezone()
    {
        $customer = Customer::factory()->create();
        $timezoneName = 'America/New_York';
        $timezone = Timezone::factory()->create(['name' => $timezoneName]);

        $location = Location::factory()->for($customer)->create();
        $location->timezoneID = $timezone->timezoneID;
        $result = $location->getTimezone();

        $this->assertEquals($timezoneName, $result);
    }

    public function testGetTimezoneWithNoRelatedTimezone()
    {
        $customer = Customer::factory()->create();
        $timezoneName = config('app.timezone');
        $timezone = Timezone::factory()->create(['name' => $timezoneName]);

        $location = Location::factory()->for($customer)->create();
        $location->timezoneID = $timezone->timezoneID;
        $result = $location->getTimezone();

        $this->assertEquals(config('app.timezone'), $result);
    }

    public function testUsersRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $user1 = User::factory()->for($customer)->for($location, 'defaultLocation')->create();
        $user2 = User::factory()->for($customer)->for($location, 'defaultLocation')->create();

        $retrievedUsers = $location->users;

        $this->assertTrue($retrievedUsers->contains($user1));
        $this->assertTrue($retrievedUsers->contains($user2));
    }

    public function testProfilesRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $profile1 = Profile::factory()->for($customer)->for($location)->create();
        $profile2 = Profile::factory()->for($customer)->for($location)->create();

        $retrievedProfiles = $location->profiles;

        $this->assertTrue($retrievedProfiles->contains($profile1));
        $this->assertTrue($retrievedProfiles->contains($profile2));
    }

    public function testTimezoneRelationship()
    {
        $customer = Customer::factory()->create();
        $timezoneName = config('app.timezone');
        $timezone = Timezone::factory()->create(['name' => $timezoneName]);

        $location = Location::factory()->for($customer)->create();
        $location->timezoneID = $timezone->timezoneID;
        $location->save();
        $retrievedTimezone = $location->timezone;

        $this->assertEquals($retrievedTimezone->timezoneID, $timezone->timezoneID);
    }
}
