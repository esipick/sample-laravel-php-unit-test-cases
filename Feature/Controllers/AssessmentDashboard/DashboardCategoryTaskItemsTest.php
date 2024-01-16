<?php

namespace Feature\Controllers\AssessmentDashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategoryTaskItem;
use App\Models\DashboardTaskSetsItem;
use App\Models\Tasks\Task;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DashboardCategoryTaskItemsTest extends TestCase
{
    public function testUpdateTaskSetTaskPriority()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->create();
        $tasks = Task::factory()->for($customer)->create(['priority' => 1]);
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($tasks)->for($user)->create();
        $dashboardTaskItem = DashboardCategoryTaskItem::factory()->for($dashboardTaskSetsItem)->for($tasks)->create();
        $tasks->dashboardCategoryTasksItemID = $dashboardTaskItem->getKey();

        Passport::actingAs(
            $user,
            ['*']
        );
        $passportUser = Passport::actingAs($user);

        $request = [
            'tasks' => [
                [
                    'priority' => $tasks->priority,
                    'dashboardCategoryTasksItemID' => $tasks->dashboardCategoryTasksItemID,
                ],

            ],
        ];

        $response = $this->actingAs($passportUser)->put('/api/dashboards/category-task-item/task/priority', $request);
        $response->assertOk();
        $response->assertJson([
            'data' => [
                'success' => true,
                'message' => 'Dashboard task priority updated successfully',
            ],
        ]);

        $this->assertDatabaseHas('dashboard_category_tasks_items', ['dashboardCategoryTasksItemID' => $tasks->dashboardCategoryTasksItemID, 'priority' => $tasks->priority]);
    }

    public function testMoveTaskFromTaskGroup()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->create();
        $tasks = Task::factory()->for($customer)->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($tasks)->for($user)->create();
        $dashboardTaskItem = DashboardCategoryTaskItem::factory()->for($dashboardTaskSetsItem)->for($tasks)->create();

        Passport::actingAs(
            $user,
            ['*']
        );
        $passportUser = Passport::actingAs($user);

        $response = $this->actingAs($passportUser)->put('/api/dashboards/category-task-item/move/'.$dashboardTaskItem->getKey());

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'Success',
            'message' => 'Task has been moved successfully.',
        ]);

        $this->assertSoftDeleted('dashboard_category_tasks_items', ['dashboardCategoryTasksItemID' => $dashboardTaskItem->getKey()]);

    }
}
