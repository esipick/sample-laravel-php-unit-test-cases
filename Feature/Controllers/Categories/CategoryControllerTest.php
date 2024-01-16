<?php

namespace Tests\Feature\Controllers;

use App\Enum\ColorType;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Topic;
use App\Models\User;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    public function testIndexFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $totalRecords = 11;
        $perPage = 6;
        Topic::factory()->times(11)->for($customer)->create();

        $requestData = [
            'perPage' => $perPage,
        ];

        $apiURL = '/api/topics?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertEquals($totalRecords, $response->json('data')['total']);
        $this->assertEquals($perPage, $response->json('data')['perPage']);
    }

    public function testIndexFunctionCaseSearch()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $searchCount = 7;
        $search = 'abcdefghij';

        Topic::factory()->times($searchCount)->for($customer)->create(['topicTitle' => \Str::random(20)]);
        Topic::factory()->times($searchCount)->for($customer)->create(['topicTitle' => \Str::random(5).$search.\Str::random(5)]);
        Topic::factory()->times($searchCount)->for($customer)->create(['topicTitle' => \Str::random(20)]);

        $requestData = [
            'search' => $search,
        ];

        $apiURL = '/api/topics?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertEquals($searchCount, $response->json('data')['total']);
    }

    public function testIndexFunctionCaseOrderBy()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $topics = Topic::factory()->times(8)->for($customer)->create();

        $requestData = [
            'orderBy' => 'asc',
            'orderByField' => 'topicTitle',
        ];

        $apiURL = '/api/topics?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = collect($topics)->sortBy('topicTitle')->pluck('topicID')->toArray();
        $responseArray = collect($response->json('data.data'))->pluck('topicID')->toArray();

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $topicTitle = \Str::random(20);
        $requestData = [
            'topicTitle' => $topicTitle,
        ];

        $apiURL = '/api/topics';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('topics', [
            'topicTitle' => $topicTitle,
        ]);
    }

    public function testUpdateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $topic = Topic::factory()->for($customer)->create();

        $topicTitle = \Str::random(20);
        $requestData = [
            'topicTitle' => $topicTitle,
        ];

        $apiURL = '/api/topics/'.$topic->topicID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('topics', [
            'topicID' => $topic->topicID,
            'topicTitle' => $topicTitle,
        ]);
    }

    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $topic = Topic::factory()->for($customer)->create();

        $apiURL = '/api/topics/'.$topic->topicID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertSoftDeleted('topics', ['topicID' => $topic->topicID]);
    }

    public function testGetCategoriesTaskProgressFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $topic = Topic::factory()->for($customer)->create();
        Topic::factory()->for($customer)->create(['topicParentID' => $topic->topicID]);
        Task::factory()->for($customer)->for($location)->for($topic)->create([
            'color' => ColorType::RED->value,
            'isTaskSet' => 0,
            'isDeprecated' => 0,
            'completedAt' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'associatedTaskCompletedAt' => null,
            'locationID' => $location->locationID,
        ]);

        Task::factory()->for($customer)->for($location)->for($topic)->create([
            'color' => ColorType::GREEN->value,
            'isTaskSet' => 0,
            'isDeprecated' => 0,
            'completedAt' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'associatedTaskCompletedAt' => null,
            'locationID' => $location->locationID,
        ]);

        Task::factory()->for($customer)->for($location)->for($topic)->create([
            'color' => ColorType::YELLOW->value,
            'isTaskSet' => 0,
            'isDeprecated' => 0,
            'completedAt' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'associatedTaskCompletedAt' => null,
            'locationID' => $location->locationID,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
        ];

        $apiURL = '/api/topics/tasks-progress?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = [
            'Red' => 33,
            'Green' => 33,
            'Yellow' => 33,
            'topicTitle' => $topic->topicTitle,
            'topicID' => $topic->topicID,
        ];

        $responseArray = $response->json('data')[0];

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testGetCategoriesTaskProgressFunctionCase2()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();

        $topic = Topic::factory()->for($customer)->create();
        $topic2 = Topic::factory()->for($customer)->create(['topicParentID' => $topic->topicID]);

        Task::factory()->for($customer)->for($location)->for($topic2)->create([
            'color' => ColorType::RED->value,
            'isTaskSet' => 0,
            'isDeprecated' => 0,
            'completedAt' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'associatedTaskCompletedAt' => null,
            'locationID' => $location->locationID,
        ]);

        Task::factory()->for($customer)->for($location)->for($topic2)->create([
            'color' => ColorType::GREEN->value,
            'isTaskSet' => 0,
            'isDeprecated' => 0,
            'completedAt' => null,
            'dueAt' => date('Y-m-d H:i:s'),
            'associatedTaskCompletedAt' => null,
            'locationID' => $location->locationID,
        ]);

        $requestData = [
            'locationID' => $location->locationID,
            'topicID' => $topic->topicID,
        ];

        $apiURL = '/api/topics/tasks-progress?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = [
            'Red' => 50,
            'Green' => 50,
            'Yellow' => 0,
            'topicTitle' => $topic2->topicTitle,
            'topicID' => $topic2->topicID,
        ];

        $responseArray = $response->json('data')[0];

        $this->assertEquals($requestArray, $responseArray);
    }
}
