<?php

namespace Feature\Controllers\Tasks;

use App\Enum\ScheduleType;
use App\Enum\TaskItemTypeEnum;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use App\Models\Tasks\TaskItemOption;
use App\Models\User;
use Tests\TestCase;

class TaskItemOptionControllerTest extends TestCase
{
    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $requestData = [
            'prompt' => ScheduleType::WEEKLY->value,
            'itemID' => $taskItem->itemID,
        ];

        $apiURL = '/api/task-item-option';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_items_options', [
            'prompt' => ScheduleType::WEEKLY->value,
            'itemID' => $taskItem->itemID,
        ]);
    }

    public function testUpdateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $insertData = ['order' => 1, 'itemOptionID' => 419, 'prompt' => 'Lorem Ipsum'];
        $taskItemOption = TaskItemOption::factory()->for($customer)->for($taskItem)->create($insertData);

        $order = rand(1, 999);
        $requestData = [
            'order' => $order,
            'itemOptionID' => TaskItemTypeEnum::CHECKBOX->value,
            'prompt' => ScheduleType::WEEKLY->value,
            'itemID' => $taskItem->itemID,
        ];

        $apiURL = '/api/task-item-option/'.$taskItemOption->optionID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_items_options', [
            'optionID' => $taskItemOption->optionID,
            'order' => $order,
            'itemOptionID' => TaskItemTypeEnum::CHECKBOX->value,
            'prompt' => ScheduleType::WEEKLY->value,
            'itemID' => $taskItem->itemID,
        ]);
    }

    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $insertData = ['order' => 1, 'itemOptionID' => 419, 'prompt' => 'Lorem Ipsum'];
        $taskItemOption = TaskItemOption::factory()->for($customer)->for($taskItem)->create($insertData);

        $apiURL = '/api/task-item-option/'.$taskItemOption->optionID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseMissing('tasks_items_options', ['optionID' => $taskItemOption->optionID]);
    }
}
