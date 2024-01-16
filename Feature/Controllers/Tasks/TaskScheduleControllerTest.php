<?php

namespace Tests\Feature\Controllers;

use App\Enum\TaskType;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskReoccur;
use App\Models\Tasks\TaskReoccurType;
use App\Models\Tasks\TasksSchedule;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class TaskScheduleControllerTest extends TestCase
{
    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $date = date('Y-m-d H:i:s');
        $requestData = [
            'type' => TaskType::ASSESSMENT->value,
            'taskID' => $task->taskID,
            'startAt' => $date,
        ];

        $apiURL = '/api/schedules';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_reoccur', [
            'type' => TaskType::ASSESSMENT->value,
            'taskID' => $task->taskID,
            'startAt' => $date,
        ]);
    }

    public function testTaskScheduleFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();
        TasksSchedule::factory()->for($customer)->for($user)->for($task)->for($taskReoccur)->create();

        $apiURL = '/api/schedules/'.$task->taskID;
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $defaultAssignee = $response->json('data')[0]['default_assignee'];
        $this->assertEquals([$user->userID, $user->userEmail], [$defaultAssignee['userID'], $defaultAssignee['userEmail']]);
    }

    public function testUpdateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();
        $tasksSchedule = TasksSchedule::factory()->for($customer)->for($task)->for($taskReoccur)->create();

        $requestData = [
            'profileID' => 'null',
            'userID' => 'null',
        ];

        $apiURL = '/api/schedules/'.$tasksSchedule->scheduleID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_schedules', [
            'scheduleID' => $tasksSchedule->scheduleID,
            'profileID' => null,
            'userID' => null,
        ]);
    }

    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();
        $tasksSchedule = TasksSchedule::factory()->for($customer)->for($task)->for($taskReoccur)->create();

        $apiURL = '/api/schedules/'.$tasksSchedule->scheduleID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseMissing('tasks_schedules', ['itemID' => $tasksSchedule->scheduleID]);
    }

    public function testUpdateStatusFunctionCaseDaily()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($customer)->for($task)->create([
            'type' => '1',
        ]);

        $requestData = [
            'enable' => true,
        ];

        $apiURL = '/api/schedules/update/status/'.$taskReoccur->reoccurID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $key = rand(0, 59);
        $taskReoccur = TaskReoccur::where('reoccurID', $taskReoccur->reoccurID)->first();

        $taskTimezone = $taskReoccur->task()->first()->location->getTimezone();
        $scheduleType = TaskReoccurType::find($taskReoccur->type);
        $scheduleDirector = new \App\Domain\TaskScheduleDirector;
        $scheduleDates = $scheduleDirector->buildSchedule($scheduleType->code, $taskReoccur, false);

        $dueAt = Carbon::parse($taskReoccur->dueAt);
        $requestArray = Carbon::parse($scheduleDates[$key], $taskTimezone)->setTime($dueAt->hour, $dueAt->minute, $dueAt->second);
        $databaseArray = TasksSchedule::where('reoccurID', $taskReoccur->reoccurID)->get()->toArray();

        $requestDate = $requestArray->format('Y-m-d\TH:i:s.u\Z');
        $databaseDate = $databaseArray[$key]['taskDueAt'];

        $this->assertEquals($requestDate, $databaseDate);
    }

    public function testUpdateStatusFunctionCaseWeekly()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($customer)->for($task)->create([
            'type' => '2',
        ]);

        $requestData = [
            'enable' => true,
        ];

        $apiURL = '/api/schedules/update/status/'.$taskReoccur->reoccurID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $key = rand(0, 59);
        $taskReoccur = TaskReoccur::where('reoccurID', $taskReoccur->reoccurID)->first();

        $taskTimezone = $taskReoccur->task()->first()->location->getTimezone();
        $scheduleType = TaskReoccurType::find($taskReoccur->type);
        $scheduleDirector = new \App\Domain\TaskScheduleDirector;
        $scheduleDates = $scheduleDirector->buildSchedule($scheduleType->code, $taskReoccur, false);

        $dueAt = Carbon::parse($taskReoccur->dueAt);
        $requestArray = Carbon::parse($scheduleDates[$key], $taskTimezone)->setTime($dueAt->hour, $dueAt->minute, $dueAt->second);
        $databaseArray = TasksSchedule::where('reoccurID', $taskReoccur->reoccurID)->get()->toArray();

        $requestDate = $requestArray->format('Y-m-d\TH:i:s.u\Z');
        $databaseDate = $databaseArray[$key]['taskDueAt'];

        $this->assertEquals($requestDate, $databaseDate);
    }

    public function testUpdateStatusFunctionCaseMonthlyOnDay()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($customer)->for($task)->create([
            'type' => '3',
            'field2' => rand(1, 9),
        ]);

        $requestData = [
            'enable' => true,
        ];

        $apiURL = '/api/schedules/update/status/'.$taskReoccur->reoccurID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $key = rand(0, 59);
        $taskReoccur = TaskReoccur::where('reoccurID', $taskReoccur->reoccurID)->first();

        $taskTimezone = $taskReoccur->task()->first()->location->getTimezone();
        $scheduleType = TaskReoccurType::find($taskReoccur->type);
        $scheduleDirector = new \App\Domain\TaskScheduleDirector;
        $scheduleDates = $scheduleDirector->buildSchedule($scheduleType->code, $taskReoccur, false);

        $dueAt = Carbon::parse($taskReoccur->dueAt);
        $requestArray = Carbon::parse($scheduleDates[$key], $taskTimezone)->setTime($dueAt->hour, $dueAt->minute, $dueAt->second);
        $databaseArray = TasksSchedule::where('reoccurID', $taskReoccur->reoccurID)->get()->toArray();

        $requestDate = $requestArray->format('Y-m-d\TH:i:s.u\Z');
        $databaseDate = $databaseArray[$key]['taskDueAt'];

        $this->assertEquals($requestDate, $databaseDate);
    }

    public function testUpdateStatusFunctionCaseMonthlyOnWeekday()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($customer)->for($task)->create([
            'type' => '4',
        ]);

        $requestData = [
            'enable' => true,
        ];

        $apiURL = '/api/schedules/update/status/'.$taskReoccur->reoccurID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $key = rand(0, 59);
        $taskReoccur = TaskReoccur::where('reoccurID', $taskReoccur->reoccurID)->first();

        $taskTimezone = $taskReoccur->task()->first()->location->getTimezone();
        $scheduleType = TaskReoccurType::find($taskReoccur->type);
        $scheduleDirector = new \App\Domain\TaskScheduleDirector;
        $scheduleDates = $scheduleDirector->buildSchedule($scheduleType->code, $taskReoccur, false);

        $dueAt = Carbon::parse($taskReoccur->dueAt);
        $requestArray = Carbon::parse($scheduleDates[$key], $taskTimezone)->setTime($dueAt->hour, $dueAt->minute, $dueAt->second);
        $databaseArray = TasksSchedule::where('reoccurID', $taskReoccur->reoccurID)->get()->toArray();

        $requestDate = $requestArray->format('Y-m-d\TH:i:s.u\Z');
        $databaseDate = $databaseArray[$key]['taskDueAt'];

        $this->assertEquals($requestDate, $databaseDate);
    }

    public function testUpdateStatusFunctionCaseAnnualOnDay()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($customer)->for($task)->create([
            'type' => '5',
            'field2' => rand(1, 9),
            'field3' => rand(1, 9),
        ]);

        $requestData = [
            'enable' => true,
        ];

        $apiURL = '/api/schedules/update/status/'.$taskReoccur->reoccurID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $key = rand(0, 59);
        $taskReoccur = TaskReoccur::where('reoccurID', $taskReoccur->reoccurID)->first();

        $taskTimezone = $taskReoccur->task()->first()->location->getTimezone();
        $scheduleType = TaskReoccurType::find($taskReoccur->type);
        $scheduleDirector = new \App\Domain\TaskScheduleDirector;
        $scheduleDates = $scheduleDirector->buildSchedule($scheduleType->code, $taskReoccur, false);

        $dueAt = Carbon::parse($taskReoccur->dueAt);
        $requestArray = Carbon::parse($scheduleDates[$key], $taskTimezone)->setTime($dueAt->hour, $dueAt->minute, $dueAt->second);
        $databaseArray = TasksSchedule::where('reoccurID', $taskReoccur->reoccurID)->get()->toArray();

        $requestDate = $requestArray->format('Y-m-d\TH:i:s.u\Z');
        $databaseDate = $databaseArray[$key]['taskDueAt'];

        $this->assertEquals($requestDate, $databaseDate);
    }

    public function testUpdateStatusFunctionCaseAnnualOnWeekday()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($customer)->for($task)->create([
            'type' => '6',
        ]);

        $requestData = [
            'enable' => true,
        ];

        $apiURL = '/api/schedules/update/status/'.$taskReoccur->reoccurID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $key = rand(0, 59);
        $taskReoccur = TaskReoccur::where('reoccurID', $taskReoccur->reoccurID)->first();

        $taskTimezone = $taskReoccur->task()->first()->location->getTimezone();
        $scheduleType = TaskReoccurType::find($taskReoccur->type);
        $scheduleDirector = new \App\Domain\TaskScheduleDirector;
        $scheduleDates = $scheduleDirector->buildSchedule($scheduleType->code, $taskReoccur, false);

        $dueAt = Carbon::parse($taskReoccur->dueAt);
        $requestArray = Carbon::parse($scheduleDates[$key], $taskTimezone)->setTime($dueAt->hour, $dueAt->minute, $dueAt->second);
        $databaseArray = TasksSchedule::where('reoccurID', $taskReoccur->reoccurID)->get()->toArray();

        $requestDate = $requestArray->format('Y-m-d\TH:i:s.u\Z');
        $databaseDate = $databaseArray[$key]['taskDueAt'];

        $this->assertEquals($requestDate, $databaseDate);
    }
}
