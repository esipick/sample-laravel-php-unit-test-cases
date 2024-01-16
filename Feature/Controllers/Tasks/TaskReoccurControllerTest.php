<?php

namespace Feature\Controllers\Tasks;

use App\Enum\TaskItemTypeEnum;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskReoccur;
use App\Models\User;
use Tests\TestCase;

class TaskReoccurControllerTest extends TestCase
{
    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();

        $apiURL = '/api/task-reoccur/'.$taskReoccur->reoccurID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertSoftDeleted('tasks_reoccur', ['reoccurID' => $taskReoccur->reoccurID]);
    }

    public function testUpdateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();

        $date = date('Y-m-d H:i:s');
        $requestData = [
            'type' => TaskItemTypeEnum::CHECKBOX->value,
            'taskID' => $task->taskID,
            'startAt' => $date,
            'spawnInterval' => '00:00:00',
        ];

        $apiURL = '/api/task-reoccur/'.$taskReoccur->reoccurID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_reoccur', [
            'reoccurID' => $taskReoccur->reoccurID,
            'type' => TaskItemTypeEnum::CHECKBOX->value,
            'taskID' => $task->taskID,
            'startAt' => $date,
            'spawnInterval' => '00:00:00',
        ]);
    }
}
