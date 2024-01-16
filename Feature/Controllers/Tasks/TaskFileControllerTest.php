<?php

namespace Feature\Controllers\Tasks;

use App\Enum\UserDurationEnum;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskFile;
use App\Models\Tasks\TaskItem;
use App\Models\User;
use Tests\TestCase;

class TaskFileControllerTest extends TestCase
{
    public function testIndexFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $taskFile[] = TaskFile::factory()->for($customer)->for($task)->for($taskItem)->create();
        $taskFile[] = TaskFile::factory()->for($customer)->for($task)->for($taskItem)->create();
        $taskFile[] = TaskFile::factory()->for($customer)->for($task)->for($taskItem)->create();
        $taskFile[] = TaskFile::factory()->for($customer)->for($task)->for($taskItem)->create();
        $taskFile[] = TaskFile::factory()->for($customer)->for($task)->for($taskItem)->create();

        $apiURL = route('tasks.index', ['itemID' => $taskItem->itemID]);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertEquals(collect($taskFile)->pluck('fileName'), collect($response->json('data'))->pluck('fileName'));
    }

    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $requestData = [
            'fileName' => UserDurationEnum::MONTHS->value,
            'uploadFileName' => UserDurationEnum::DAYS->value,
            'itemID' => $taskItem->itemID,
            'taskID' => $task->taskID,
            'customerID' => $customer->customerID,
        ];

        $apiURL = 'api/task-files';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_files', [
            'fileName' => UserDurationEnum::MONTHS->value,
            'uploadFileName' => UserDurationEnum::DAYS->value,
            'itemID' => $taskItem->itemID,
            'taskID' => $task->taskID,
            'customerID' => $customer->customerID,
        ]);
    }

    public function testTaskFileShowFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskFile = TaskFile::factory()->for($customer)->for($task)->create([
            'uploadFileName' => UserDurationEnum::DAYS->value,
        ]);

        $apiURL = 'api/task-files/'.$taskFile->fileID;
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

    }

    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $taskFile = TaskFile::factory()->for($customer)->for($task)->for($taskItem)->create();

        $apiURL = 'api/task-files/'.$taskFile->fileID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertSoftDeleted('tasks_files', ['fileID' => $taskFile->fileID]);
    }
}
