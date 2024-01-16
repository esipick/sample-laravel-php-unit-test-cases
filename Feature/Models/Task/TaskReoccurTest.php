<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskAuditSchedulelog;
use App\Models\Tasks\TaskReoccur;
use App\Models\Tasks\TaskReoccurType;
use App\Models\Tasks\TasksSchedule;
use App\Models\User;
use Tests\TestCase;

class TaskReoccurTest extends TestCase
{
    public function testCreateTaskReoccur()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $reoccurType = TaskReoccurType::find(1);

        $taskReoccur = TaskReoccur::create([
            'startDate' => '2023-10-25',
            'field1' => 1,
            'taskID' => $task->taskID,
            'customerID' => $customer->customerID,
            'type' => $reoccurType->reoccurTypeID,
        ]);

        $this->assertInstanceOf(TaskReoccur::class, $taskReoccur);
        $this->assertEquals('2023-10-25', $taskReoccur->startDate);
        $this->assertEquals(1, $taskReoccur->field1);
    }

    public function testUpdateTaskReoccur()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();

        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();
        $newStartDate = '2023-10-26';

        $taskReoccur->update(['startDate' => $newStartDate]);

        $updatedTaskReoccur = TaskReoccur::find($taskReoccur->reoccurID);

        $this->assertEquals($newStartDate, $updatedTaskReoccur->startDate);
    }

    public function testDeleteTaskReoccur()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();

        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();

        $this->assertTrue($taskReoccur->delete());
        $this->assertNull(TaskReoccur::find($taskReoccur->reoccurID));
    }

    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();

        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();

        $this->assertInstanceOf(Task::class, $taskReoccur->task);
        $this->assertEquals($task->taskID, $taskReoccur->task->taskID);
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();

        $taskReoccur = TaskReoccur::factory()->for($task)->for($customer)->create();

        $this->assertInstanceOf(Customer::class, $taskReoccur->customer);
        $this->assertEquals($customer->customerID, $taskReoccur->customer->customerID);
    }

    public function testTaskItemOptionRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $reoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        $tasksSchedule = TasksSchedule::factory()->for($customer)->for($task)->for($reoccur)->create();

        $retrievedTasksSchedule = $reoccur->taskItemOption;
        $this->assertTrue($retrievedTasksSchedule->is($tasksSchedule));

    }

    public function testTaskAuditScheduleLogsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $reoccur = TaskReoccur::factory()->for($customer)->for($task)->create();

        $auditScheduleLog1 = TaskAuditSchedulelog::factory()->for($user)->for($customer)->create(['reoccurID' => $reoccur->reoccurID, 'scheduleLogNotes' => 'unit test']);
        $auditScheduleLog2 = TaskAuditSchedulelog::factory()->for($user)->for($customer)->create(['reoccurID' => $reoccur->reoccurID, 'scheduleLogNotes' => 'unit test']);

        $retrievedAuditScheduleLogs = $reoccur->taskAuditScheduleLogs;

        $this->assertTrue($retrievedAuditScheduleLogs->contains($auditScheduleLog1));
        $this->assertTrue($retrievedAuditScheduleLogs->contains($auditScheduleLog2));
    }

    public function testTaskSchedulesRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $reoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        // Create related TaskSchedules
        $schedule1 = TasksSchedule::factory()->for($customer)->for($task)->for($reoccur)->create();
        $schedule2 = TasksSchedule::factory()->for($customer)->for($task)->for($reoccur)->create();

        // Retrieve the associated TaskSchedules through the relationship
        $retrievedSchedules = $reoccur->taskSchedules;

        $this->assertTrue($retrievedSchedules->contains($schedule1));
        $this->assertTrue($retrievedSchedules->contains($schedule2));
    }
}
