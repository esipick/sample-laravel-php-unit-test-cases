<?php

namespace Tests\Feature\Controllers\Users;

use App\Hashing\CustomHasher;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PasswordReset;
use App\Models\Profile;
use App\Models\Security;
use App\Models\SocialiteClient;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function testIndexMethodReturnsUsers()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->get('/api/users?perPage=5');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'userFirstName' => $user->userFirstName,
            'userLastName' => $user->userLastName,
        ]);
    }
    public function testIndexMethodFiltersUsersByLocation()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->get('/api/users?locationID='.$location->locationID);
        $response->assertStatus(200);
    }
    public function testIndexMethodFiltersUsersBySearch()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->get('/api/users?search=test');
        $response->assertStatus(500);
    }
    public function testIndexMethodFiltersUsersStatus()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userFirstName' => 'test']);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->get('/api/users?status=Active');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testIndexMethodFiltersUsersOrderByUserFullName()
    {
        $customer = Customer::factory()->create();
        $user1 = User::factory()->for($customer)->create(['userFirstName' => 'Test1']);
        $user2 = User::factory()->for($customer)->create(['userFirstName' => 'Test2']);
        $headers = $this->authenticateUser($user1);

        $response = $this->withHeaders($headers)->get('/api/users?status=Active&orderByField=userFullName&orderBy=asc');
        $response->assertStatus(200);
        $data = $response->json('data.data');

        $users = array_column($data, 'userFirstName');

        $this->assertEquals([$user1->userFirstName, $user2->userFirstName], $users);
    }
    public function testIndexMethodFiltersUsersOrderByUserActive()
    {
        $customer = Customer::factory()->create();
        $user1 = User::factory()->for($customer)->create(['userFirstName' => 'Test1']);
        $user2 = User::factory()->for($customer)->create(['userFirstName' => 'Test2']);
        $headers = $this->authenticateUser($user1);

        $response = $this->withHeaders($headers)->get('/api/users?status=Active&orderByField=userActive&orderBy=desc');
        $response->assertStatus(200);
        $data = $response->json('data.data');
    }
    public function testIndexMethodFiltersUsersOrderByUserInActive()
    {
        $customer = Customer::factory()->create();
        $user1 = User::factory()->for($customer)->create(['userFirstName' => 'Test1','userActive'=>1]);
       User::factory()->for($customer)->create(['userFirstName' => 'Test2','userActive'=>0]);
        $headers = $this->authenticateUser($user1);

        $response = $this->withHeaders($headers)->get('/api/users?status=Inactive');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }
    public function testIndexMethodFiltersUsersOrderByUserAll()
    {
        $customer = Customer::factory()->create();
        $user1 = User::factory()->for($customer)->create(['userFirstName' => 'Test1','userActive'=>1]);
       User::factory()->for($customer)->create(['userFirstName' => 'Test2','userActive'=>0]);
        $headers = $this->authenticateUser($user1);

        $response = $this->withHeaders($headers)->get('/api/users?status=All');
        $response->assertStatus(200);
    }

    public function testInfoMethodReturnsUserInformation()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->get('/api/users/info');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'userLocationCreds',
                'userInfo',
                'assignedLocations',
                'profileLocations',
                'userProfiles',
                'profileParents',
                'isTaskBoardUser',
                'allowedMaxFileUploadMb',
            ],
        ]);
    }
    public function testInfoMethodFiltersLocationsForNonSuperAdminUser()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);
        $response = $this->withHeaders($headers)->get('/api/users/info');

        $response->assertStatus(200);
    }


    public function testStoreMethodCreatesUser()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);


        $userData = [
            'userEmail' => 'newuser@example.com',
            'userLoginName' => 'newuser',
            'userFirstName' => 'userFirstName',
            'userLastName' => 'userLastName',
        ];

        $response = $this->withHeaders($headers)->post('/api/users', $userData);

        $response->assertStatus(200); // Assuming a successful response status
        $this->assertDatabaseHas('users', ['userEmail' => 'newuser@example.com', 'userLoginName' => 'newuser']);
    }
    public function testStoreMethodHandlesDuplicateUserEmailOrLoginName()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userEmail' => 'duplicate@example.com',
            'userLoginName' => 'duplicateuser']);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $userData = [
            'userEmail' => 'duplicate@example.com',
            'userLoginName' => 'duplicateuser',
            'userFirstName' => 'userFirstName',
            'userLastName' => 'userLastName',
        ];
        $this->withHeaders($headers)->post('/api/users', $userData);
        $response = $this->withHeaders($headers)->post('/api/users', $userData);

        $response->assertStatus(500);
    }
    public function testUpdateMethodUpdatesUser()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $userToUpdate = User::factory()->for($customer)->create();

        $updatedUserData = [
            'userFirstName' => 'John',
            'userLastName' => 'Doe',
        ];
        $response = $this->withHeaders($headers)->put("/api/users/{$userToUpdate->userID}", $updatedUserData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', $updatedUserData);
    }
    public function testChangePasswordMethodChangesPassword()
    {
        $customHasher = new CustomHasher;
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $currentPassword = 'oldPassword';
        $newPassword = 'newPassword';

        $requestData = [
            'currentPassword' => $currentPassword,
            'password' => $newPassword,
        ];
        $salt = bin2hex(random_bytes(15));
        $salt = hash('sha256', $salt);
        $user->userPassword = $customHasher->make($currentPassword, [], $salt);
        $user->save();

        $response = $this->withHeaders($headers)->post('/api/users/change-password', $requestData);

        $response->assertStatus(200);

        $this->assertTrue($customHasher->check($newPassword, $user->userPassword));
    }

    public function testChangePasswordMethodHandlesIncorrectCurrentPassword()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $newPassword = 'newPassword';

        $requestData = [
            'currentPassword' => 'incorrectPassword',
            'password' => $newPassword,
        ];

        $response = $this->withHeaders($headers)->post('/api/users/change-password', $requestData);

        $response->assertStatus(422);
    }
    public function testSendResetLinkEmailMethodSendsResetLink()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example111.com']);
        $user = User::factory()->for($customer)->create(['userActive' => 1, 'userApproved' => 1]);
        $headers = $this->authenticateUser($user);
        $headers['Origin'] = 'http://example111.com';
        $requestData = [
            'email' => $user->userEmail,
        ];
        $response = $this->withHeaders($headers)->post('/api/auth/forgot-password', $requestData);
        $response->assertStatus(200);
    }
    public function testSendResetLinkEmailMethodSendsResetLinkWrongDomain()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example2.com']);
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);
        $headers['Origin'] = 'http://example1.com';
        $requestData = [
            'email' => $user->userEmail,
        ];
        $response = $this->withHeaders($headers)->post('/api/auth/forgot-password', $requestData);

        $response->assertStatus(500);
    }

    public function testSendResetLinkEmailMethodSendsResetLinkInvalidEmail()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example3.com']);
        $customer1 = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example4.com']);
        $user = User::factory()->for($customer)->create();
        $user1 = User::factory()->for($customer1)->create(['userApproved' => 0]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);
        $headers['Origin'] = 'http://example3.com';
        $requestData = [
            'email' => $user1->userEmail,
        ];
        $response = $this->withHeaders($headers)->post('/api/auth/forgot-password', $requestData);

        $response->assertStatus(500);
    }
    public function testSendResetLinkEmailMethodSendsResetLinkIsAzureUser()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example3.com']);
        $customer1 = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example4.com']);
        $user = User::factory()->for($customer)->create();
        $user1 = User::factory()->for($customer1)->create(['userApproved' => 1, 'isAzureUser' => true]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);
        $headers['Origin'] = 'http://example4.com';
        $requestData = [
            'email' => $user1->userEmail,
        ];

        $response = $this->withHeaders($headers)->post('/api/auth/forgot-password', $requestData);
        $response->assertStatus(500);
    }
    public function testResetMethodResetsPassword()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example112.com']);
        $user = User::factory()->for($customer)->create(['userActive' => 1, 'userApproved' => 1]);
        $headers = $this->authenticateUser($user);
        $origin = $customer->domain;
        $headers['Origin'] = "https://$origin";

        $token = Str::random(15);
        PasswordReset::create([
            'userEmail' => $user->userEmail,
            'token' => $token,
        ]);

        $requestData = [
            'token' => $token,
            'password' => 'newPassword',
        ];

        $response = $this->withHeaders($headers)->post('/api/auth/reset-password', $requestData);

        $response->assertStatus(200);
        $customHasher = new CustomHasher;
        $user->refresh();
        $this->assertTrue($customHasher->check('newPassword', $user->userPassword));
    }
    public function testResetMethodHandlesInvalidToken()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example112.com']);
        $user = User::factory()->for($customer)->create(['userActive' => 1, 'userApproved' => 1]);
        $headers = $this->authenticateUser($user);
        $origin = $customer->domain;
        $headers['Origin'] = "https://$origin";

        $requestData = [
            'token' => 'invalidToken',
            'password' => 'newPassword',
        ];

        $response = $this->withHeaders($headers)->post('/api/auth/reset-password', $requestData);

        $response->assertStatus(400);
    }
    public function testResetPasswordMethodResetsUserPassword()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example3.com']);
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);
        $origin = $customer->domain;
        $headers['Origin'] = "https://$origin";

        $response = $this->withHeaders($headers)->put("/api/users/reset-password/{$user->userID}");
        $response->assertStatus(200);
    }
    public function testResetPasswordMethodHandlesAuthorization()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example113.com']);
        $origin = $customer->domain;
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);
        $headers['Origin'] = "https://$origin";
        $response = $this->withHeaders($headers)->put("/api/users/reset-password/{$user->userID}");

        $response->assertStatus(403);
    }
    public function testResetPasswordMethodHandlesAuthorizationInvalidDomain()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example112.com']);
        $user = User::factory()->for($customer)->create(['userActive' => 1, 'userApproved' => 1]);
        $headers = $this->authenticateUser($user);

        $headers['Origin'] = "https://example113.com";

        $token = Str::random(15);
        PasswordReset::create([
            'userEmail' => $user->userEmail,
            'token' => $token,
        ]);

        $requestData = [
            'token' => $token,
            'password' => 'newPassword',
        ];

        $response = $this->withHeaders($headers)->post('/api/auth/reset-password', $requestData);
        $response->assertStatus(500);
    }

    public function testGetUserProfileSettingsMethodReturnsUserSettings()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example112.com']);
        $user = User::factory()->for($customer)->create(['userActive' => 1, 'userApproved' => 1, 'notifyEmailAssignmentGroup' => 1, 'notifyEmailAssignmentUser' => 0, 'notifyEmailOther' => 0, 'notifyEmailReminder' => 1]);
        $headers = $this->authenticateUser($user);

        $expectedSettings = [
            'notifyEmailAssignmentGroup' => $user->notifyEmailAssignmentGroup,
            'notifyEmailAssignmentUser' => $user->notifyEmailAssignmentUser,
            'notifyEmailOther' => $user->notifyEmailOther,
            'notifyEmailReminder' => $user->notifyEmailReminder,
        ];

        $response = $this->withHeaders($headers)->get('/api/users/profile-settings');
        $response->assertStatus(200);
        $response->assertJson(['data' => $expectedSettings]);
    }

    public function testUpdateUserProfileSettingsMethodUpdatesUserSettings()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example112.com']);
        $user = User::factory()->for($customer)->create(['userActive' => 1, 'userApproved' => 1, 'notifyEmailAssignmentGroup' => 1, 'notifyEmailAssignmentUser' => 0, 'notifyEmailOther' => 1, 'notifyEmailReminder' => 1]);
        $headers = $this->authenticateUser($user);

        $updatedSettings = [
            'notifyEmailAssignmentGroup' => true,
            'notifyEmailAssignmentUser' => false,
            'notifyEmailOther' => true,
            'notifyEmailReminder' => true,
        ];

        $response = $this->withHeaders($headers)->put('/api/users/profile-settings', $updatedSettings);

        $response->assertStatus(200);

        $user->refresh();

        $this->assertEquals($updatedSettings['notifyEmailAssignmentGroup'], $user->notifyEmailAssignmentGroup);
        $this->assertEquals($updatedSettings['notifyEmailAssignmentUser'], $user->notifyEmailAssignmentUser);
        $this->assertEquals($updatedSettings['notifyEmailOther'], $user->notifyEmailOther);
        $this->assertEquals($updatedSettings['notifyEmailReminder'], $user->notifyEmailReminder);
    }

    public function testProfilesMethodReturnsUserProfiles()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example3.com']);
        $customer1 = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example4.com']);
        $user = User::factory()->for($customer)->create();
        $user1 = User::factory()->for($customer1)->create(['userApproved' => 1, 'isAzureUser' => true]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->get('/api/users/profiles');

        $response->assertStatus(200);
    }
    public function testDestroyMethodDeletesUser()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example3.com']);
        $customer1 = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example4.com']);
        $user = User::factory()->for($customer)->create();
        $user1 = User::factory()->for($customer1)->create(['userApproved' => 1, 'isAzureUser' => true]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->delete("/api/users/{$user->userID}");

        $response->assertStatus(200);

    }
}
