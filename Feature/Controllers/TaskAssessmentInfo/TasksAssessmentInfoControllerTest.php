<?php

namespace Feature\Controllers\TaskAssessmentInfo;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksAssessmentInfo;
use App\Models\User;
use Tests\TestCase;

class TasksAssessmentInfoControllerTest extends TestCase
{
    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $score = rand(1, 99);
        $observation = \Str::random(20);
        $recommendation = \Str::random(20);

        $requestData = [
            'taskID' => $task->taskID,
            'userID' => $user->userID,
            'score' => $score,
            'observation' => $observation,
            'recommendation' => $recommendation,
        ];

        $apiURL = '/api/task-assessment-info';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertStatus(201);
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_assessment_info', [
            'taskID' => $task->taskID,
            'userID' => $user->userID,
            'score' => $score,
            'observation' => $observation,
            'recommendation' => $recommendation,
        ]);
    }

    public function testStoreFunctionCaseException()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $score = rand(1, 99);
        $observation = \Str::random(20);
        $recommendation = \Str::random(20);

        TasksAssessmentInfo::factory()->for($task)->for($user)->create([
            'score' => $score,
            'observation' => $observation,
            'recommendation' => $recommendation,
        ]);

        $requestData = [
            'taskID' => $task->taskID,
            'userID' => $user->userID,
            'score' => $score,
            'observation' => $observation,
            'recommendation' => $recommendation,
        ];

        $apiURL = '/api/task-assessment-info';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertServerError();
    }

    public function testUpdateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $tasksAssessmentInfo = TasksAssessmentInfo::factory()->for($task)->for($user)->create([
            'score' => rand(1, 99),
            'observation' => \Str::random(20),
            'recommendation' => \Str::random(20),
        ]);

        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $score = rand(1, 99);
        $observation = \Str::random(20);
        $recommendation = \Str::random(20);

        $requestData = [
            'taskID' => $task->taskID,
            'userID' => $user->userID,
            'score' => $score,
            'observation' => $observation,
            'recommendation' => $recommendation,
        ];

        $apiURL = '/api/task-assessment-info/'.$tasksAssessmentInfo->taskAssessmentId;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_assessment_info', [
            'taskID' => $task->taskID,
            'userID' => $user->userID,
            'score' => $score,
            'observation' => $observation,
            'recommendation' => $recommendation,
        ]);
    }

    public function testIndexFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $tasksAssessmentInfo = TasksAssessmentInfo::factory()->times(rand(1, 10))->for($task)->for($user)->create([
            'score' => rand(1, 99),
            'observation' => \Str::random(20),
            'recommendation' => \Str::random(20),
        ]);

        $requestData = [
            'taskID' => $task->taskID,
        ];

        $apiURL = '/api/task-assessment-info?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $requestArray = $tasksAssessmentInfo->pluck('taskAssessmentId')->toArray();
        $responseArray = collect($response->json('data'))->pluck('taskAssessmentId')->toArray();

        sort($requestArray);
        sort($responseArray);

        $this->assertEquals($requestArray, $responseArray);
    }
}
