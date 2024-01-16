<?php

namespace Feature\Controllers\Auth;

use App\Hashing\CustomHasher;
use App\Http\Controllers\AuthController;
use App\Models\Customer;
use App\Models\SocialiteClient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function testAuthRedirectSocialite()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example1.com']);
        $user = User::factory()->for($customer)->create();
        $response = $this->withHeaders(['Origin' => 'http://example1.com'])->post('/api/auth/login', [
            'username' => $user->userLoginName,
            'password' => 'password',
        ]);
        $response->assertStatus(200)->assertJson([
            'message' => 'You have successfully logged in!',
        ]);
    }

    public function testLoginWithValidCredentialsInvalidDomain()
    {
        $customHasher = new CustomHasher;
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example4.com']);
        $salt = bin2hex(random_bytes(15));
        $salt = hash('sha256', $salt);
        $passwordString = 'password';
        $userPassword = $customHasher->make($passwordString, [], $salt);
        User::create([
            'notifyEmailAssignmentGroup' => fake()->numberBetween(0, 1),
            'notifyEmailAssignmentUser' => fake()->numberBetween(0, 1),
            'notifyEmailOther' => fake()->numberBetween(0, 1),
            'notifyEmailReminder' => fake()->numberBetween(0, 1),
            'userActive' => 1,
            'userApproved' => fake()->numberBetween(0, 1),
            'userFirstName' => fake()->firstName(),
            'userLastName' => fake()->lastName(),
            'userLoginAttempt' => fake()->numberBetween(0, 100),
            'userLoginName' => fake()->userName(),
            'userPhone' => fake()->phoneNumber(),
            'userLogins' => fake()->numberBetween(0, 100),
            'userType' => 'super_admin',
            'customerID' => $customer->customerID,
            'userEmail' => 'test@example.com',
            'userPassword' => $userPassword,
        ]);

        $response = $this->withHeaders(['Origin' => 'http://example11.com'])->post('/api/auth/login', [
            'username' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(500);
    }

    public function testLoginWithValidCredentialsInvalidCustomerDomain()
    {
        $customHasher = new CustomHasher;
        $customer1 = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'test.com']);
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example5.com']);
        $salt = bin2hex(random_bytes(15));
        $salt = hash('sha256', $salt);
        $passwordString = 'password';
        $userPassword = $customHasher->make($passwordString, [], $salt);
        User::create([
            'notifyEmailAssignmentGroup' => fake()->numberBetween(0, 1),
            'notifyEmailAssignmentUser' => fake()->numberBetween(0, 1),
            'notifyEmailOther' => fake()->numberBetween(0, 1),
            'notifyEmailReminder' => fake()->numberBetween(0, 1),
            'userActive' => 1,
            'userApproved' => fake()->numberBetween(0, 1),
            'userFirstName' => fake()->firstName(),
            'userLastName' => fake()->lastName(),
            'userLoginAttempt' => fake()->numberBetween(0, 100),
            'userLoginName' => fake()->userName(),
            'userPhone' => fake()->phoneNumber(),
            'userLogins' => fake()->numberBetween(0, 100),
            'userType' => 'super_admin',
            'customerID' => $customer->customerID,
            'userEmail' => 'test@example.com',
            'userPassword' => $userPassword,
        ]);

        $response = $this->withHeaders(['Origin' => 'http://test.com'])->post('/api/auth/login', [
            'username' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(500);
    }

    public function testLoginWithValidCredentialsInvalidCredentials()
    {
        $customHasher = new CustomHasher;
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example2.com']);
        $salt = bin2hex(random_bytes(15));
        $salt = hash('sha256', $salt);
        $passwordString = 'password';
        $userPassword = $customHasher->make($passwordString, [], $salt);
        User::create([
            'notifyEmailAssignmentGroup' => fake()->numberBetween(0, 1),
            'notifyEmailAssignmentUser' => fake()->numberBetween(0, 1),
            'notifyEmailOther' => fake()->numberBetween(0, 1),
            'notifyEmailReminder' => fake()->numberBetween(0, 1),
            'userActive' => 1,
            'userApproved' => fake()->numberBetween(0, 1),
            'userFirstName' => fake()->firstName(),
            'userLastName' => fake()->lastName(),
            'userLoginAttempt' => fake()->numberBetween(0, 100),
            'userLoginName' => 'test2@example.com',
            'userPhone' => fake()->phoneNumber(),
            'userLogins' => fake()->numberBetween(0, 100),
            'userType' => 'super_admin',
            'customerID' => $customer->customerID,
            'userEmail' => 'test2@example.com',
            'userPassword' => $userPassword,
        ]);

        $response = $this->withHeaders(['Origin' => 'http://example2.com'])->post('/api/auth/login', [
            'username' => 'test2@example.com',
            'password' => 'password11',
        ]);
        $response->assertStatus(500);
    }

    public function testLoginWithInvalidCredentials()
    {
        $response = $this->postJson(route('login'), [
            'username' => 'nonexistent@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(500);
    }

    public function testAuthRedirect()
    {
        Customer::factory()->create([
            'domain' => 'example3.com',
        ]);

        $response = $this->get('/api/auth/azure/redirect', [
            'Referer' => 'http://example3.com',
        ]);

        $response->assertRedirect();
    }

    public function testAuthRedirectSocialiteClient()
    {
        Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example4.com']);

        $response = $this->get('/api/auth/azure/redirect', [
            'Referer' => 'http://example4.com',
        ]);

        $response->assertRedirect();
    }

    public function testAuthRedirectWrongDomain()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example5.com']);
        $response = $this->get('/api/auth/azure/redirect', [
            'Referer' => 'http://test1.com',
        ]);

        $response->assertRedirect();
    }

    public function testHandleProviderCallback()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example6.com']);

        $response = $this->get('/azure/callback', [
            'state' => 'example6.com',
        ]);

        $response->assertRedirect();
    }

    public function testHandleInvalidProviderCallback()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example6.com']);
        $response = $this->get('/azure/callback', [
            'state' => 'example16.com',
        ]);

        $response->assertRedirect();
    }

    public function testHandleValidProviderCallback()
    {
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => 'example4.com']);
        $response = $this->get('/azure/callback', [
            'state' => 'example4.com',
        ]);

        $response->assertRedirect();
    }

    public function testHandleProviderCallbackWithValidState()
    {
        $state = 'example.com';
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => $state]);
        Socialite::shouldReceive('driver')
            ->with('azure')
            ->andReturn(new class
            {
                public function stateless()
                {
                    return $this;
                }

                public function user()
                {
                    return (object) ['user' => 'testuser'];
                }
            });

        $controller = new AuthController;
        $response = $controller->handleProviderCallback('azure');

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testHandleProviderCallbackWithInvalidState()
    {
        $state = 'invalid.com';
        Socialite::shouldReceive('driver')
            ->with('azure')
            ->andThrow(new \Exception('Azure Login error'));

        $controller = new AuthController;
        $response = $controller->handleProviderCallback('azure');

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testFindOrCreateUserWithOktaProvider()
    {
        $user = (object) [
            'email' => 'test@example.com',
            'sub' => '12345',
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
        ];
        $state = 'example.com';
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => $state]);
        Socialite::shouldReceive('driver')
            ->with('okta');

        $controller = new AuthController;
        $localUser = $controller->findOrCreateUser($user, $state, $customer, 'okta');

        $this->assertInstanceOf(User::class, $localUser);
        $this->assertEquals('test@example.com', $localUser->userEmail);
    }

    public function testFindOrCreateUserWithAzureProvider()
    {
        $user = (object) [
            'mail' => 'test@example.com',
            'userPrincipalName' => 'test@example.com',
            'id' => '12345',
            'displayName' => 'Test User',
            'givenName' => 'Test',
            'surname' => 'User',
            'mobilePhone' => '12345678',
        ];
        $state = 'example.com';
        $customer = Customer::factory()->has(SocialiteClient::factory())->create(['domain' => $state]);
        Socialite::shouldReceive('driver')
            ->with('azure');

        $controller = new AuthController;
        $localUser = $controller->findOrCreateUser($user, $state, $customer, 'azure');

        $this->assertInstanceOf(User::class, $localUser);
        $this->assertEquals('test@example.com', $localUser->userEmail);
    }

    public function testRefreshTokenSuccess()
    {
        $client = DB::table('oauth_clients')->insert([
            'id' => 1,
            'name' => '7777',
            'secret' => 'your-secret-key',
            'password_client' => 1,
            'redirect' => 'iii',
            'personal_access_client' => 'personal_access_client',
            'revoked' => 0,
        ]);

        $data = [
            'refreshToken' => 'valid-refresh-token',
        ];

        Http::fake([
            '*' => Http::response(['access_token' => 'new-access-token', 'refresh_token' => 'new-refresh-token'], 200),
        ]);

        $response = $this->postJson('/api/auth/refresh-token', $data);

        $response->assertStatus(200);

        $response->assertJson(['status' => 'Success']);
    }

    public function testRefreshTokenValidation()
    {
        $data = [];

        $response = $this->postJson('/api/auth/refresh-token', $data);

        $response->assertStatus(400);
    }

    public function testRefreshTokenMissingClient()
    {

        $client = DB::table('oauth_clients')->insert([
            'id' => 2,
            'name' => '7777',
            'secret' => 'your-secret-key',
            'password_client' => 1,
            'redirect' => 'iii',
            'personal_access_client' => 'personal_access_client',
            'revoked' => 0,
        ]);
        DB::table('oauth_clients')->where('password_client', 1)->delete();

        $data = [
            'refreshToken' => 'valid-refresh-token',
        ];

        $response = $this->postJson('/api/auth/refresh-token', $data);

        $response->assertStatus(422);
    }

    public function testRefreshTokenInvalidRefreshToken()
    {

        $client = DB::table('oauth_clients')->insert([
            'id' => 3,
            'name' => '7777',
            'secret' => 'your-secret-key',
            'password_client' => 1,
            'redirect' => 'iii',
            'personal_access_client' => 'personal_access_client',
            'revoked' => 0,
        ]);

        $data = [
            'refreshToken' => 'invalid-refresh-token',
        ];

        Http::fake([
            '*' => Http::response(['message' => 'Invalid refresh token'], 403),
        ]);

        $response = $this->postJson('/api/auth/refresh-token', $data);

        $response->assertStatus(403);
    }
}
