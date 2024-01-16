<?php

namespace Feature\Controllers\Users;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\User;
use Tests\TestCase;

class TaskBoardUsersAndProfilesControllerTest extends TestCase
{
    public function testIndexFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create([
            'locationDefaultUser' => $user->userID,
        ]);

        $profile = Profile::factory()->for($customer)->create();
        Security::factory()->for($location)->for($profile)->for($user)->create();

        $apiURL = '/api/task-board-users';
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = [
            'userID' => $user->userID,
            'userFirstName' => $user->userFirstName,
            'userLastName' => $user->userLastName,
            'userEmail' => $user->userEmail,
            'userFullName' => $user->userFullName,
        ];

        $responseArray = collect($response->json('data.data.users.data'))->toArray();
        $responseArray = [
            'userID' => $responseArray[0]['userID'],
            'userFirstName' => $responseArray[0]['userFirstName'],
            'userLastName' => $responseArray[0]['userLastName'],
            'userEmail' => $responseArray[0]['userEmail'],
            'userFullName' => $responseArray[0]['userFullName'],
        ];

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseperPage()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create([
            'locationDefaultUser' => $user->userID,
        ]);

        $profile = Profile::factory()->for($customer)->create();
        Security::factory()->for($location)->for($profile)->for($user)->create();

        $perPage = 10;
        $requestData = [
            'perPage' => $perPage,
        ];

        $apiURL = '/api/task-board-users?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $responseArray = collect($response->json('data.data.users'))->toArray();

        $this->assertEquals($perPage, $responseArray['per_page']);
    }

    public function testIndexFunctionCaseOrderBy()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create([
            'locationDefaultUser' => $user->userID,
        ]);

        $profile = Profile::factory()->for($customer)->create();
        Security::factory()->for($location)->for($profile)->for($user)->create();

        $requestData = [
            'orderBy' => 'desc',
            'orderByField' => 'userEmail',
        ];

        $apiURL = '/api/task-board-users?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $responseArray = collect($response->json('data.data.users.data'))->toArray();

        $this->assertEquals($user->userEmail, $responseArray[0]['userEmail']);
    }

    public function testIndexFunctionCaseException()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $apiURL = '/api/task-board-users';
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertServerError();
    }
}
