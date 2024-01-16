<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use App\Models\Tasks\TasksReportCustom;
use App\Models\Tasks\TasksReportCustomItem;
use Tests\TestCase;

class TasksReportCustomTest extends TestCase
{
    public function testCreateTasksReportCustom()
    {
        $customer = Customer::factory()->create();
        $reportCustom = TasksReportCustom::factory()->for($customer)->create();

        $this->assertInstanceOf(TasksReportCustom::class, $reportCustom);
        $this->assertDatabaseHas('tasks_report_custom', ['customReportID' => $reportCustom->customReportID]);
    }

    public function testTasksReportCustomItemsRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();

        $reportCustom = TasksReportCustom::factory()->for($customer)->has(TasksReportCustomItem::factory()->for($taskItem, 'taskItem')->count(5))->create();
        $this->assertInstanceOf(TasksReportCustomItem::class, $reportCustom->tasksReportCustomItems->first());
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $reportCustom = TasksReportCustom::factory()->for($customer)->create();
        $this->assertInstanceOf(Customer::class, $reportCustom->customer);
    }

    public function testFillableFields()
    {
        $reportCustom = new TasksReportCustom;
        $fillable = ['customerID', 'customReportName'];
        $notFillable = array_diff($reportCustom->getFillable(), $fillable);

        $this->assertEquals($fillable, $reportCustom->getFillable());
        $this->assertEmpty($notFillable);
    }
}
