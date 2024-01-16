<?php

namespace Tests\Feature\Controllers;

use App\Enum\TaskItemTypeEnum;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategory;
use App\Models\DashboardCategoryTaskItem;
use App\Models\DashboardTaskSetCategory;
use App\Models\DashboardTaskSetsItem;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class TasksetGroupControllerTest extends TestCase
{
    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $dashboard = Dashboard::factory()->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();

        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->create();
        DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem)->for($dashboardCategory)->create([
            'dashboardItemID' => $dashboardTaskSetsItem->dashboardItemID,
        ]);

        $requestData = [
            'dashboardItemID' => $dashboardTaskSetsItem->dashboardItemID,
            'taskSetID' => $task->taskID,
        ];

        $apiURL = '/api/dashboard-taskset-group';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('dashboard_tasksets_categories', [
            'dashboardItemID' => $dashboardTaskSetsItem->dashboardItemID,
            'taskSetID' => $task->taskID,
        ]);
    }

    public function testStoreFunction2()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $dashboard = Dashboard::factory()->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();

        $task2 = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $requestData = [
            'dashboardItemID' => $dashboardTaskSetsItem->dashboardItemID,
            'taskSetID' => $task2->taskID,
        ];

        $apiURL = '/api/dashboard-taskset-group';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertServerError();
    }

    public function testAddTaskToTaskSetGroupFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $dashboard = Dashboard::factory()->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();

        $dashboardCategory2 = DashboardCategory::factory()->for($dashboard)->create();
        DashboardCategoryTaskItem::factory()->for($task)->for($dashboardCategory2)->create();

        $requestData = [
            'categoryID' => $dashboardCategory->categoryID,
            'taskID' => $task->taskID,
            'taskSetID' => $dashboardTaskSetsItem->taskSetID,
        ];

        $apiURL = '/api/add-task-taskset-group';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('dashboard_category_tasks_items', [
            'taskID' => $task->taskID,
            'categoryID' => $dashboardCategory->categoryID,
        ]);
    }

    public function testAddTaskToTaskSetGroupFunction2()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $dashboard = Dashboard::factory()->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();

        DashboardCategoryTaskItem::factory()->for($task)->for($dashboardCategory)->create();

        $requestData = [
            'categoryID' => $dashboardCategory->categoryID,
            'taskID' => $task->taskID,
            'taskSetID' => $dashboardTaskSetsItem->taskSetID,
        ];

        $apiURL = '/api/add-task-taskset-group';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertServerError();
    }

    public function testUnlinkTaskSetDashboardFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task_ = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $task = Task::factory()->for($customer)->for($location)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'templateID' => $task_->taskID,
        ]);

        $dashboard = Dashboard::factory()->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create([
            'taskSetID' => $task->templateID,
        ]);

        $requestData = [
            'taskSetID' => $dashboardTaskSetsItem->taskSetID,
        ];

        $apiURL = '/api/unlink-task-set-dashboard';
        $response = $this->withHeaders($headers)->patch($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseMissing('dashboard_tasksets_items', ['itemID' => $task->taskSetID]);
    }

    public function testUnlinkTaskSetDashboardFunctionCase2()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $task->update(['templateID' => $task->taskID]);

        $dashboard = Dashboard::factory()->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create([
            'taskSetID' => $task->templateID,
        ]);

        $task2 = Task::factory()->for($customer)->for($location)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'templateID' => $task->taskID,
        ]);

        DashboardTaskSetsItem::factory()->for($dashboard)->for($task2)->for($user)->create(['taskSetID' => $task2->taskID]);

        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->create();
        DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem)->for($dashboardCategory)->create([
            'taskSetID' => $task2->taskID,
        ]);

        $requestData = [
            'taskSetID' => $dashboardTaskSetsItem->taskSetID,
        ];

        $apiURL = '/api/unlink-task-set-dashboard';
        $response = $this->withHeaders($headers)->patch($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseMissing('dashboard_tasksets_items', ['itemID' => $task->taskSetID]);
    }

    public function testUnlinkTaskSetDashboardFunctionCase3()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $task2 = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $dashboard = Dashboard::factory()->create();
        DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create([
            'taskSetID' => $task->taskID,
        ]);

        $requestData = [
            'taskSetID' => $task2->taskID,
        ];

        $apiURL = '/api/unlink-task-set-dashboard';
        $response = $this->withHeaders($headers)->patch($apiURL, $requestData);

        $response->assertServerError();
    }

    public function testUnlinkTaskFromTaskSetFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        Task::factory()->for($customer)->for($location)->create([
            'dueAt' => date('Y-m-d H:i:s'),
            'templateID' => $task->taskID,
        ]);

        $dashboard = Dashboard::factory()->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->create();

        DashboardCategoryTaskItem::factory()->for($task)->for($dashboardCategory)->create();

        $requestData = [
            'dashboardCategoryID' => $dashboardCategory->categoryID,
            'taskID' => $task->taskID,
        ];

        $apiURL = '/api/unlink-task-from-task-set';
        $response = $this->withHeaders($headers)->patch($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('dashboard_category_tasks_items', [
            'taskID' => $task->taskID,
            'categoryID' => null,
        ]);
    }

    public function testUpdatePriorityFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $dashboard = Dashboard::factory()->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->create();

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();
        $dashboardTaskSetCategory = DashboardTaskSetCategory::factory()->for($task)->for($dashboardTaskSetsItem)->for($dashboardCategory)->create();

        $requestData['taskgroups'][] = [
            'priority' => TaskItemTypeEnum::DROPDOWN->value,
            'categoryID' => $dashboardTaskSetCategory->categoryID,
        ];

        $apiURL = '/api/update-priority';
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['data']);

        $this->assertDatabaseHas('dashboard_tasksets_categories', [
            'priority' => TaskItemTypeEnum::DROPDOWN->value,
            'categoryID' => $dashboardTaskSetCategory->categoryID,
        ]);
    }
}
