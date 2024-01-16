<?php

namespace Feature\Controllers\Others;

use App\Models\Customer;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use App\Models\Tasks\TasksReportCustom;
use App\Models\Tasks\TasksReportCustomItem;
use App\Models\User;
use Tests\TestCase;

class ItemReportControllerTest extends TestCase
{
    public function testIndexItemReportAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        TasksReportCustom::factory()->for($customer)->count(3)->create();

        $response = $this->withHeaders($headers)->get('/api/task-item-report');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function testStoreItemReportAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();
        $requestData = [
            'name' => 'Custom Report Name',
            'itemBatchID' => $taskItem->itemBatchID,
        ];

        $response = $this->withHeaders($headers)->post('/api/task-item-report', $requestData);

        $response->assertStatus(200);
    }

    public function testShowItemReportAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $tasksReportCustom = TasksReportCustom::factory()->for($customer)->create();
        TasksReportCustomItem::factory()->for($tasksReportCustom)->create();

        $response = $this->withHeaders($headers)->get("/api/task-item-report/{$tasksReportCustom->customReportID}");

        $response->assertStatus(200);
    }
}
