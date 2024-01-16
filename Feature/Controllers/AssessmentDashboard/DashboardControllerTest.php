<?php

namespace Feature\Controllers\AssessmentDashboard;

use App\Enum\CredEnum;
use App\Models\Cred;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategory;
use App\Models\DashboardTaskSetCategory;
use App\Models\DashboardTaskSetsItem;
use App\Models\Location;
use App\Models\Profile;
use App\Models\ProfileCred;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\User;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    public function testDashboardIndexAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->get('/api/dashboards?perPage=10&page=1');

        $response->assertStatus(200);
    }

    public function testDashboardLocationIndexAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $creds = Cred::all();
        $creds->each(function ($cred) use ($profile) {
            ProfileCred::factory()->for($profile)->for($cred)->create();
        });
        $headers = $this->authenticateUser($user);
        Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $response = $this->withHeaders($headers)->get('/api/dashboards?perPage=10&page=1&locationID='.$location->locationID);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testDashboardLocationReadOnlyIndexAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $creds = Cred::all();
        $creds->each(function ($cred) use ($profile) {
            if (in_array($cred->credID, [CredEnum::AD_READ_ONLY->value])) {
                ProfileCred::factory()->for($profile)->for($cred)->create();
            } else {
                ProfileCred::factory()->for($profile)->for($cred)->create(['profileCredStatus' => 0]);
            }
        });
        $headers = $this->authenticateUser($user);
        Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $response = $this->withHeaders($headers)->get('/api/dashboards?perPage=10&page=1&locationID='.$location->locationID);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testDashboardLocationNotGlobalIndexAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $creds = Cred::all();
        $creds->each(function ($cred) use ($profile) {
            if (in_array($cred->credID, [CredEnum::AD_READ_ONLY->value, CredEnum::AD_ADMIN_PERSONAL->value, CredEnum::AD_ADMIN_ALL->value])) {
                ProfileCred::factory()->for($profile)->for($cred)->create(['profileCredStatus' => 0]);
            } else {
                ProfileCred::factory()->for($profile)->for($cred)->create(['profileCredStatus' => 2]);
            }
        });
        $headers = $this->authenticateUser($user);
        Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $response = $this->withHeaders($headers)->get('/api/dashboards?perPage=10&page=1&locationID='.$location->locationID);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testDashboardLocationIndexSearchAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        Dashboard::factory()->for($customer)->for($location)->for($user)->create(['dashboardName' => 'test']);

        $response = $this->withHeaders($headers)->get('/api/dashboards?perPage=10&page=1&search=test&orderBy=desc&orderByField=created_at');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testDashboardLocationIndexGlobalAssessmentDashboardsAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $creds = Cred::all();
        $creds->each(function ($cred) use ($profile) {
            ProfileCred::factory()->for($profile)->for($cred)->create(['profileCredStatus' => 2]);
        });

        $headers = $this->authenticateUser($user);
        Dashboard::factory()->for($customer)->for($location)->for($user)->create(['dashboardName' => 'test']);

        $response = $this->withHeaders($headers)->get('/api/dashboards?perPage=10&page=1&globalAssessmentDashboards=1&orderBy=desc&orderByField=created_at');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testDashboardLocationIndexStatusAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        Dashboard::factory()->for($customer)->for($location)->for($user)->create(['dashboardName' => 'test']);

        $response = $this->withHeaders($headers)->get('/api/dashboards?perPage=10&page=1&status=Active&orderBy=desc&orderByField=created_at');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testDashboardLocationIndexStatusInactiveAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        Dashboard::factory()->for($customer)->for($location)->for($user)->create(['dashboardName' => 'test', 'archivedAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/dashboards?perPage=10&page=1&status=Inactive&orderBy=desc&orderByField=created_at');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testDashboardLocationIndexStatusPublishedAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        Dashboard::factory()->for($customer)->for($location)->for($user)->create(['dashboardName' => 'test', 'publishedAt' => now()]);

        $response = $this->withHeaders($headers)->get('/api/dashboards?perPage=10&page=1&status=Published&orderBy=desc&orderByField=dashboardStatus');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testDashboardShowAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $response = $this->withHeaders($headers)->get("/api/dashboards/{$dashboard->dashboardID}");

        $response->assertStatus(200);
    }

    public function testDashboardStoreAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $requestData = [
            'dashboardName' => fake()->name,
            'otherFields' => 'other_values',
            'locationID' => $location->locationID,
        ];

        $response = $this->withHeaders($headers)->post('/api/dashboards', $requestData);

        $response->assertStatus(200)->assertJson(['status' => 'Success']);
        $this->assertDatabaseHas('dashboards', ['dashboardName' => $requestData['dashboardName']]);
    }

    public function testDashboardUpdateAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $requestData = [
            'dashboardName' => fake()->name,
            'otherFields' => 'other_updated_values',
        ];

        $response = $this->withHeaders($headers)->put("/api/dashboards/{$dashboard->dashboardID}", $requestData);

        $response->assertStatus(200);
    }

    public function testDashboardUpdateArchivedAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $requestData = [
            'dashboardName' => fake()->name,
            'otherFields' => 'other_updated_values',
            'isArchived' => true,
            'isPublished' => false,
        ];

        $response = $this->withHeaders($headers)->put("/api/dashboards/{$dashboard->dashboardID}", $requestData);

        $response->assertStatus(200);
    }

    public function testDashboardUpdatePublishedAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $requestData = [
            'dashboardName' => fake()->name,
            'otherFields' => 'other_updated_values',
            'isPublished' => true,
            'isArchived' => false,
        ];

        $response = $this->withHeaders($headers)->put("/api/dashboards/{$dashboard->dashboardID}", $requestData);

        $response->assertStatus(200);
    }

    public function testDestroyAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $response = $this->withHeaders($headers)->delete("/api/dashboards/{$dashboard->dashboardID}");

        $response->assertStatus(200);
    }

    public function testUpdatePriorityAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $dashboardCategory = DashboardCategory::factory()->for($customer)->for($dashboard)->create();

        $taskSet1 = Task::factory()->for($customer)->for($dashboard)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem1 = DashboardTaskSetsItem::factory()->for($taskSet1)->for($user)->for($dashboard)->create();
        DashboardTaskSetCategory::factory()->for($taskSet1)->for($dashboardTaskSetsItem1, 'dashboardTasksetsItem')->for($dashboardCategory)->create();

        $taskSet2 = Task::factory()->for($customer)->for($dashboard)->create(['isTaskSet' => 1]);
        $dashboardTaskSetsItem2 = DashboardTaskSetsItem::factory()->for($taskSet2)->for($user)->for($dashboard)->create();
        DashboardTaskSetCategory::factory()->for($taskSet2)->for($dashboardTaskSetsItem2, 'dashboardTasksetsItem')->for($dashboardCategory)->create();

        $requestData = [
            'tasksets' => [
                [
                    'taskSetID' => $taskSet1->taskID,
                    'priority' => 1,
                ],
                [
                    'taskSetID' => $taskSet2->taskID,
                    'priority' => 2,
                ],
            ],
        ];

        $response = $this->withHeaders($headers)->put("/api/dashboards/update-task-set-priority/{$dashboard->dashboardID}", $requestData);

        $response->assertStatus(200);
    }

    public function testUpdatePriorityInvalidItemAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $taskSet1 = Task::factory()->for($customer)->for($dashboard)->create(['isTaskSet' => 1]);
        $taskSet2 = Task::factory()->for($customer)->for($dashboard)->create(['isTaskSet' => 1]);

        $requestData = [
            'tasksets' => [
                [
                    'taskSetID' => $taskSet1->taskID,
                    'priority' => 1,
                ],
                [
                    'taskSetID' => $taskSet2->taskID,
                    'priority' => 2,
                ],
            ],
        ];

        $response = $this->withHeaders($headers)->put("/api/dashboards/update-task-set-priority/{$dashboard->dashboardID}", $requestData);

        $response->assertStatus(422);
    }

    public function testUpdateDashboardScoreAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $requestData = [
            'maximumScore' => 100,
            'minimumScore' => 0,
        ];

        $response = $this->withHeaders($headers)->put("/api/dashboards/score/{$dashboard->dashboardID}", $requestData);

        $response->assertStatus(200);
    }
}
