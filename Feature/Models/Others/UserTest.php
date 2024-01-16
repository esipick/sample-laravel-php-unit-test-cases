<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\LicenseIndustry;
use App\Models\LicenseUser;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskDelay;
use App\Models\Tasks\TasksComment;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $this->assertInstanceOf(User::class, $user);
    }

    public function testUserPasswordValidation()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $this->assertTrue($user->validateForPassportPasswordGrant('password'));
    }

    public function testUserPasswordValidationFailCase()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create([
            'userApproved' => true,
            'isAzureUser' => true,
        ]);

        $this->assertTrue($user->validateForPassportPasswordGrant('password'));
    }

    public function testUserFullNameAttribute()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create([
            'userFirstName' => 'John',
            'userLastName' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->userFullName);
    }

    public function testFindForPassport()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userApproved' => 1]);

        $userEmail = 'terlumina-'.$user->customerID.$user->userID.'@mailinator.com';
        $foundUser = $user->findForPassport($userEmail);

        $this->assertInstanceOf(User::class, $foundUser);
    }

    public function testFindUserWithLocalhostDomain()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userApproved' => 1]);

        $userEmail = 'terlumina-'.$user->customerID.$user->userID.'@mailinator.com';
        $foundUser = $user->findUserWithDomain($userEmail, 'localhost');

        $this->assertInstanceOf(User::class, $foundUser);
    }

    public function testFindUserWithDomain()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userApproved' => 1]);

        $userEmail = 'terlumina-'.$user->customerID.$user->userID.'@mailinator.com';
        $foundUser = $user->findUserWithDomain($userEmail, $customer->domain);

        $this->assertInstanceOf(User::class, $foundUser);
    }

    public function testToSearchableArray()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create([
            'userFirstName' => 'John',
            'userLastName' => 'Doe',
        ]);

        $userEmail = 'terlumina-'.$user->customerID.$user->userID.'@mailinator.com';
        $searchableArray = $user->toSearchableArray();

        $this->assertEquals([
            'userFirstName' => 'John',
            'userLastName' => 'Doe',
            'userEmail' => $userEmail,
        ], $searchableArray);
    }

    public function testFindUsernameEmail()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $userEmail = 'terlumina-'.$user->customerID.$user->userID.'@mailinator.com';
        $foundUser = $user->findUsernameEmail($userEmail);

        $this->assertInstanceOf(User::class, $foundUser);
    }

    public function testGetAuthPassword()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $authPassword = $user->getAuthPassword();

        $this->assertEquals($user->userPassword, $authPassword);
    }

    public function testBelongsToRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create(['userDefaultLocationID' => $location->locationID]);

        $this->assertInstanceOf(Location::class, $user->defaultLocation);
        $this->assertInstanceOf(Customer::class, $user->customer);
    }

    public function testHasManyRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create(['userDefaultLocationID' => $location->locationID]);

        $profile = Profile::factory()->for($user)->for($customer)->for($location)->create();
        $this->assertInstanceOf(Profile::class, $user->profiles[0]);

        Security::factory()->for($user)->for($profile)->for($location)->create();
        $this->assertInstanceOf(Security::class, $user->securities[0]);

        $task = Task::factory()->for($user)->for($customer)->create();
        $this->assertInstanceOf(Task::class, $user->tasks[0]);

        TaskDelay::factory()->for($user)->for($customer)->for($task)->create();
        $this->assertInstanceOf(TaskDelay::class, $user->taskDelays[0]);

        Dashboard::factory()->for($user)->for($customer)->create();
        $this->assertInstanceOf(Dashboard::class, $user->dashboards[0]);

        $licenseIndustry = LicenseIndustry::factory()->create();
        LicenseUser::factory()->for($user)->for($licenseIndustry)->create();
        $this->assertInstanceOf(LicenseUser::class, $user->licenses[0]);

        TasksComment::factory()->for($user)->for($task)->create();
        $this->assertInstanceOf(TasksComment::class, $user->taskComments[0]);

    }
}
