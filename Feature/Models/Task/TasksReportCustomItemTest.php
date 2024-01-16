<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use App\Models\Tasks\TasksReportCustom;
use App\Models\Tasks\TasksReportCustomItem;
use Tests\TestCase;

class TasksReportCustomItemTest extends TestCase
{
    public function testCreateTasksReportCustomItem()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();
        $reportCustom = TasksReportCustom::factory()->for($customer)->create();
        $reportCustomItem = TasksReportCustomItem::factory()->for($reportCustom)->for($taskItem)->create();

        $this->assertInstanceOf(TasksReportCustomItem::class, $reportCustomItem);
        $this->assertDatabaseHas('tasks_report_custom_items', ['customItemID' => $reportCustomItem->customItemID]);
    }

    public function testTasksReportCustomRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();
        $reportCustom = TasksReportCustom::factory()->for($customer)->create();
        $reportCustomItem = TasksReportCustomItem::factory()->for($reportCustom)->for($taskItem)->create();
        $this->assertInstanceOf(TasksReportCustom::class, $reportCustomItem->tasksReportCustom);
    }

    public function testTaskItemRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create();
        $reportCustom = TasksReportCustom::factory()->for($customer)->create();
        $reportCustomItem = TasksReportCustomItem::factory()->for($reportCustom)->for($taskItem)->create();
        $this->assertInstanceOf(TaskItem::class, $reportCustomItem->taskItem);
    }

    public function testFillableFields()
    {
        $reportCustomItem = new TasksReportCustomItem;
        $fillable = ['customReportName', 'customReportID', 'itemBatchID'];
        $notFillable = array_diff($reportCustomItem->getFillable(), $fillable);

        $this->assertEquals($fillable, $reportCustomItem->getFillable());
        $this->assertEmpty($notFillable);
    }
}
