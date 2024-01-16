<?php

namespace Feature\Controllers\AssessmentDashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategory;
use App\Models\DashboardCategoryTaskItem;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class DashboardCategoryTaskItemControllerTest extends TestCase
{
    public function testUpdateTaskSetTaskPriorityAction()
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

        $dashboardCategoryTaskItems = DashboardCategoryTaskItem::factory()->for($task)->for($dashboardCategory)->count(3)->create();

        $requestData = [
            'tasks' => [
                [
                    'dashboardCategoryTasksItemID' => $dashboardCategoryTaskItems[0]->dashboardCategoryTasksItemID,
                    'priority' => 1,
                ],
                [
                    'dashboardCategoryTasksItemID' => $dashboardCategoryTaskItems[1]->dashboardCategoryTasksItemID,
                    'priority' => 2,
                ],
                [
                    'dashboardCategoryTasksItemID' => $dashboardCategoryTaskItems[2]->dashboardCategoryTasksItemID,
                    'priority' => 3,
                ],
            ],
        ];

        $response = $this->withHeaders($headers)->put('/api/dashboards/category-task-item/task/priority', $requestData);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => true,
            'message' => 'Task priority has been updated.',
        ]);
    }

    public function testMoveTaskFromTaskGroupAction()
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

        $dashboardCategoryTaskItem = DashboardCategoryTaskItem::factory()->for($task)->for($dashboardCategory)->create();

        $response = $this->withHeaders($headers)->put("/api/dashboards/category-task-item/move/{$dashboardCategoryTaskItem->dashboardCategoryTasksItemID}");

        // Assert the response status code and content
        $response->assertStatus(200);
        $response->assertJson([
            'data' => true,
            'message' => 'Task has been moved successfully.',
        ]);
    }
}
