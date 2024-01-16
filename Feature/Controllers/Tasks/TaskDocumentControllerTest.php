<?php

namespace Feature\Controllers\Tasks;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskDocument;
use App\Models\User;
use Tests\TestCase;

class TaskDocumentControllerTest extends TestCase
{
    /** @test */
    public function testTaskDocumentsFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $document = Document::factory()->for($customer)->create();
        TaskDocument::factory()->for($task)->for($document)->create();

        $apiURL = route('task-document', ['task' => $task]);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    /** @test */
    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();

        $document = Document::factory()->for($customer)->create();
        TaskDocument::factory()->for($task)->for($document)->create();

        $requestData = [
            'taskID' => $task->taskID,
            'documentID' => $task->taskID,
        ];

        $apiURL = 'api/task-document';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);

        // Task Document has already added.
        $apiURL = 'api/task-document';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertStatus(500);
    }

    /** @test */
    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();

        $document = Document::factory()->for($customer)->create();
        TaskDocument::factory()->for($task)->for($document)->create();

        $apiURL = 'api/task-document/'.$task->taskID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }
}
