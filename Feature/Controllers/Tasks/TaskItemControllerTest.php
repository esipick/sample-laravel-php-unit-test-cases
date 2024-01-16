<?php

namespace Feature\Controllers\Tasks;

use App\Enum\TaskItemTypeEnum;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use App\Models\Tasks\TaskItemOption;
use App\Models\Tasks\TaskItemType;
use App\Models\User;
use Tests\TestCase;

class TaskItemControllerTest extends TestCase
{
    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $requestData = [
            'taskID' => $task->taskID,
            'itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value,
            'prompt' => $task->name,
            'parentTaskItemID' => $task->taskID,
        ];

        $apiURL = '/api/task-item/';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_items', [
            'itemID' => $response->json('data')['itemID'],
            'taskID' => $task->taskID,
            'itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value,
            'parentTaskItemID' => $task->taskID,
        ]);
    }

    public function testStoreFunctionCase2()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItemType = TaskItemType::factory()->create();

        $requestData = [
            'taskID' => $task->taskID,
            'itemTypeID' => $taskItemType->itemTypeID,
        ];

        $apiURL = '/api/task-item/';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertServerError();
    }

    public function testUpdateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $requestData = [
            'taskID' => $task->taskID,
            'itemTypeID' => TaskItemTypeEnum::TASK->value,
            'itemDeadlineDate' => 1,
            'response' => 'NULL',
            'profileID' => $profile->profileID,
            'userID' => $user->userID,
        ];

        $apiURL = '/api/task-item/'.$taskItem->itemID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_items', [
            'itemID' => $response->json('data')['itemID'],
            'taskID' => $task->taskID,
            'itemDeadlineDate' => 1,
            'response' => null,
        ]);
    }

    public function testUpdateFunctionCase2()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create([
            'itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value,
            'itemDeadlineDate' => 1,
        ]);

        $requestData = [
            'taskID' => $task->taskID,
            'itemTypeID' => TaskItemTypeEnum::DATEPICKER2->value,
            'response' => date('Y-m-d H:i:s'),
            'profileID' => $profile->profileID,
            'userID' => $user->userID,
        ];

        $apiURL = '/api/task-item/'.$taskItem->itemID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_items', [
            'itemID' => $response->json('data')['itemID'],
            'taskID' => $task->taskID,
            'itemTypeID' => TaskItemTypeEnum::DATEPICKER2->value,
            'response' => date('Y-m-d H:i:s'),
        ]);
    }

    public function testUpdateFunctionCase3()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create(
            ['itemTypeID' => TaskItemTypeEnum::ASSIGNTASK->value]
        );

        $requestData = [
            'taskID' => $task->taskID,
            'itemTypeID' => TaskItemTypeEnum::REASSIGN->value,
            'response' => 'NULL',
            'profileID' => $profile->profileID,
            'userID' => $user->userID,
        ];

        $apiURL = '/api/task-item/'.$taskItem->itemID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_items', [
            'itemID' => $response->json('data')['itemID'],
            'taskID' => $task->taskID,
            'itemTypeID' => TaskItemTypeEnum::REASSIGN->value,
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

        TaskItem::factory()->for($customer)->for($task)->create([
            'parentTaskItemID' => $taskItem->taskItemID,
        ]);

        $apiURL = '/api/task-item/'.$taskItem->itemID;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertSoftDeleted('tasks_items', ['itemID' => $taskItem->itemID]);
    }

    public function testDuplicateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();
        $taskItemChild = TaskItem::factory()->for($customer)->for($task)->create([
            'parentTaskItemID' => $taskItem->itemID,
        ]);

        $insertData = [
            'order' => $task->color,
            'itemOptionID' => $task->locationID,
            'prompt' => $task->name,
        ];
        TaskItemOption::factory()->for($customer)->for($taskItemChild)->create($insertData);
        TaskItemOption::factory()->for($customer)->for($taskItemChild)->create($insertData);
        TaskItemOption::factory()->for($customer)->for($taskItemChild)->create($insertData);

        $requestData = [
            'taskID' => $task->taskID,
            'itemTypeID' => TaskItemTypeEnum::REASSIGN->value,
            'response' => 'NULL',
            'userID' => $user->userID,
        ];

        TaskItem::factory()->for($customer)->for($task)->create([
            'parentTaskItemID' => $taskItemChild->taskItemID,
        ]);

        $apiURL = '/api/task-item-duplicate/'.$taskItemChild->itemID;
        $response = $this->withHeaders($headers)->patch($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $count = TaskItem::where([
            'itemTypeID' => $taskItemChild->itemTypeID,
            'prompt' => $taskItemChild->prompt,
            'itemBatchID' => $taskItemChild->itemBatchID,
            'customerID' => $taskItemChild->customerID,
            'taskID' => $taskItemChild->taskID,
            'parentTaskItemID' => $taskItemChild->parentTaskItemID,
            'updated_at' => $taskItemChild->updated_at,
            'created_at' => $taskItemChild->created_at,
        ])->count();

        $this->assertTrue($count == 2);
    }

    public function testDuplicateFunctionCase2()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $requestData = [
            'taskID' => $task->taskID,
            'itemTypeID' => TaskItemTypeEnum::REASSIGN->value,
            'response' => 'NULL',
            'userID' => $user->userID,
        ];

        $apiURL = '/api/task-item-duplicate/'.$taskItem->itemID;
        $response = $this->withHeaders($headers)->patch($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $count = TaskItem::where([
            'itemTypeID' => $taskItem->itemTypeID,
            'prompt' => $taskItem->prompt,
            'itemBatchID' => $taskItem->itemBatchID,
            'customerID' => $taskItem->customerID,
            'taskID' => $taskItem->taskID,
            'parentTaskItemID' => $taskItem->parentTaskItemID,
            'updated_at' => $taskItem->updated_at,
            'created_at' => $taskItem->created_at,
        ])->count();

        $this->assertTrue($count == 2);
    }

    public function testAssignLinkFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $linkedTask = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $requestData = [
            'linkedTaskID' => $linkedTask->taskID,
        ];

        $apiURL = '/api/task-item/assign-link/'.$taskItem->itemID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_items', [
            'itemID' => $taskItem->itemID,
            'prompt' => $linkedTask->name,
            'taskToSpawn' => $linkedTask->batchID,
        ]);
    }

    public function testAssignLinkFunctionCase2()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $requestData = [
            'linkedTaskID' => $task->taskID,
        ];

        $apiURL = '/api/task-item/assign-link/'.$taskItem->itemID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertServerError();
    }

    public function testAssignLinkFunctionCase3()
    {
        $customer_ = Customer::factory()->create();
        $user_ = User::factory()->for($customer_)->create();
        $headers = $this->authenticateUser($user_);

        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $requestData = [
            'linkedTaskID' => $task->taskID,
        ];

        $apiURL = '/api/task-item/assign-link/'.$taskItem->itemID;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertServerError();
    }

    public function testUpdatePriorityFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $insertData = [
            'order' => $task->color,
            'itemOptionID' => $task->locationID,
            'prompt' => $task->name,
        ];
        TaskItemOption::factory()->for($customer)->for($taskItem)->create($insertData);

        $taskItemChild = TaskItem::factory()->for($customer)->for($task)->create([
            'parentTaskItemID' => $taskItem->itemID,
            'parentItemOptionID' => $taskItem->taskItemOptions[0]->optionID,
        ]);

        TaskItemOption::factory()->for($customer)->for($taskItemChild)->create($insertData);
        TaskItemOption::factory()->for($customer)->for($taskItemChild)->create($insertData);

        $requestData = ['taskItems' => [$taskItemChild->toArray()]];

        $apiURL = '/api/task-item/priority';
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('tasks_items', [
            'itemID' => $taskItemChild->itemID,
            'parentTaskItemID' => $taskItem->itemID,
            'parentItemOptionID' => $taskItem->taskItemOptions[0]->optionID,
        ]);
    }

    public function testUpdatePriorityFunctionCase2()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $requestData = ['taskItems' => [$taskItem]];

        $apiURL = '/api/task-item/priority';
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertServerError();
    }
}
