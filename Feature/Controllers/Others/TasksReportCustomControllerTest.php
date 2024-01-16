<?php

namespace Feature\Controllers\Others;

use App\Models\Customer;
use App\Models\Tasks\TasksReportCustom;
use App\Models\User;
use Tests\TestCase;

class TasksReportCustomControllerTest extends TestCase
{
    public function testDetailAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $report = TasksReportCustom::factory()->for($customer)->create();

        $response = $this->withHeaders($headers)->get('/api/custom-reports/detail?reportID='.$report->customReportID);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testDetailActionWithNonExistingReport()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $response = $this->withHeaders($headers)->get('/api/custom-reports/detail?reportID=999');

        $response->assertStatus(422);
    }
}
