<?php

namespace Feature\Controllers\AssessmentDashboard;

use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardColor;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\User;
use Tests\TestCase;

class DashboardColorControllerTest extends TestCase
{
    public function testDashboardColorStoreAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $requestData = [
            'dashboardID' => $dashboard->dashboardID,
            'from' => 0,
            'to' => 100,
            'colorCode' => '#FF0000',
            'colorLabel' => 'Red',
        ];

        $response = $this->withHeaders($headers)->post('/api/dashboard-color', $requestData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'dashboardID',
                'dashboardColor',
            ],
            'message',
        ]);
    }

    public function testDashboardColorStoreInBulkAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $requestData = [
            'dashboardID' => $dashboard->dashboardID,
            'colorRanges' => [
                [
                    'from' => 0,
                    'to' => 25,
                    'colorCode' => '#FF0000',
                    'colorLabel' => 'Red',
                ],
                [
                    'from' => 25,
                    'to' => 50,
                    'colorCode' => '#00FF00',
                    'colorLabel' => 'Green',
                ],
                [
                    'from' => 50,
                    'to' => 75,
                    'colorCode' => '#0000FF',
                    'colorLabel' => 'Blue',
                ],
            ],
            'maximumScore' => 100,
            'minimumScore' => 0,
        ];

        $response = $this->withHeaders($headers)->post('/api/dashboard-color/multiple', $requestData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'dashboardID',
                'dashboardColor',
            ],
            'message',
        ]);
    }

    public function testDashboardColorUpdateAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardColor = DashboardColor::factory()->for($dashboard)->create();

        $requestData = [
            'from' => 0,
            'to' => 25,
            'colorCode' => '#FFFF00',
            'colorLabel' => 'Yellow',
            'dashboardID' => $dashboard->dashboardID,
        ];

        $response = $this->withHeaders($headers)->put("/api/dashboard-color/{$dashboardColor->dashboardID}", $requestData);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'dashboardID',
                'dashboardColor',
            ],
            'message',
        ]);
    }

    public function testGetDashboardColorsAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();

        $response = $this->withHeaders($headers)->get("/api/get-dashboard-colors?dashboardID={$dashboard->dashboardID}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    public function testGetDashboardDefaultColorsAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        DashboardColor::factory()->for($dashboard)->count(3)->create();

        $response = $this->withHeaders($headers)->get('/api/dashboard-default-colors');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    public function testDashboardDefaultDestroyAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        $dashboard = Dashboard::factory()->for($customer)->for($location)->for($user)->create();
        $dashboardColor = DashboardColor::factory()->for($dashboard)->create();

        $response = $this->withHeaders($headers)->delete("/api/dashboard-color/{$dashboardColor->colorID}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'message',
        ]);
    }
}
