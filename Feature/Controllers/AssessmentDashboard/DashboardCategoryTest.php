<?php

namespace Feature\Controllers\AssessmentDashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategory;
use App\Models\DashboardTaskSetCategory;
use App\Models\DashboardTaskSetsItem;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DashboardCategoryTest extends TestCase
{
    public function testShowDashboardCategory()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->create(['categoryName' => 'Real Category']);
        $task = Task::factory()->for($customer)->for($location)->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();
        DashboardTaskSetCategory::factory()->for($dashboardTaskSetsItem)->for($dashboardCategory)->for($task)->create();
        $task->refresh();

        Passport::actingAs(
            $user,
            ['*']
        );
        $passportUser = Passport::actingAs($user);
        $response = $this->actingAs($passportUser)->get('/api/dashboards/category/'.$dashboardCategory->categoryID);
        $response->assertOk();
    }

    public function testUpdateDashboardCategory()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->create();
        $dashboardCategory = DashboardCategory::factory()->create(['categoryName' => 'Real Category', 'dashboardID' => $dashboard->dashboardID]);

        Passport::actingAs(
            $user,
            ['*']
        );
        $passportUser = Passport::actingAs($user);

        $request = [
            'categoryName' => 'Test Category',
        ];

        $response = $this->actingAs($passportUser)->put('/api/dashboards/category/'.$dashboardCategory->categoryID, $request);
        $response->assertOk();
        $response->assertJson([
            'data' => [
                'categoryName' => 'Test Category',
            ],
        ]);
    }

    public function testDestroyDashboardCategory()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->create();
        $dashboardCategory = DashboardCategory::factory()->for($dashboard)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();
        DashboardTaskSetCategory::factory()->for($dashboardTaskSetsItem)->for($dashboardCategory)->for($task)->create();

        Passport::actingAs(
            $user,
            ['*']
        );
        $passportUser = Passport::actingAs($user);

        $response = $this->actingAs($passportUser)->delete('/api/dashboards/category/'.$dashboardCategory->categoryID);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'Success',
            'message' => 'Dashboard task group deleted successfully',
            'data' => true,
        ]);

        $this->assertDatabaseMissing('dashboard_categories', ['id' => $dashboardCategory->categoryID]);
        $this->assertDatabaseMissing('dashboard_category_tasks_items', ['category_id' => $dashboardCategory->categoryID]);
        $this->assertDatabaseMissing('dashboard_tasksets_categories', ['category_id' => $dashboardCategory->categoryID]);
    }
}
