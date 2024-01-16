<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'TestSeeder']);
        Artisan::call('passport:install:auto');
    }

    protected function authenticateUser(User $user)
    {
        Passport::actingAs($user);
        $tokenResult = $user->createToken('Login_Token');

        return ['Authorization' => "Bearer $tokenResult->accessToken"];
    }
}
