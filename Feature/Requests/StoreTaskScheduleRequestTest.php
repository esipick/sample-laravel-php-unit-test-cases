<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreTaskScheduleRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskReoccurType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTaskScheduleRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreTaskScheduleRequest();

        $this->assertTrue($request->authorize());
    }

    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $scheduleType = TaskReoccurType::find(1);
        $validData = [
            'type' => $scheduleType->reoccurTypeID,
            'taskID' => $task->taskID,
            'field1' => 5,
            'field2' => 'value2',
            'field3' => 'value3',
            'field4' => 'value4',
            'startAt' => now()->format('Y-m-d H:i:s'),
            'reoccurAt' => '12:00:00',
            'spawnInterval' => '01:30:00',
            'dueAt' => '23:59:59',
        ];

        $validator = Validator::make($validData, (new StoreTaskScheduleRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testOptionalFieldsCanBeOmitted()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $scheduleType = TaskReoccurType::find(1);
        $dataWithoutOptionalFields = [
            'type' => $scheduleType->reoccurTypeID,
            'taskID' => $task->taskID,
            'startAt' => now()->format('Y-m-d H:i:s'),
        ];

        $validator = Validator::make($dataWithoutOptionalFields, (new StoreTaskScheduleRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testInvalidDataFailsValidation()
    {
        $invalidData = [
            'type' => 'invalid', // 'type' should be an integer
            'taskID' => 'invalid', // 'taskID' should be an integer
            'field1' => 0, // 'field1' should be greater than or equal to 1
            'startAt' => 'invalid_date_format', // 'startAt' should be in the format 'Y-m-d H:i:s'
            'reoccurAt' => 'invalid_time_format', // 'reoccurAt' should be in the format 'H:i:s'
            'spawnInterval' => 'invalid_time_format', // 'spawnInterval' should be in the format 'H:i:s'
            'dueAt' => 'invalid_time_format', // 'dueAt' should be in the format 'H:i:s'
        ];

        $validator = Validator::make($invalidData, (new StoreTaskScheduleRequest())->rules());

        $this->assertTrue($validator->fails());

    }

    public function testNonExistingTaskFailsValidation()
    {
        $scheduleType = TaskReoccurType::find(1);
        $nonExistingData = [
            'type' => $scheduleType->reoccurTypeID,
            'taskID' => 999, // Assuming this task ID does not exist
            'startAt' => now()->format('Y-m-d H:i:s'),
        ];

        $validator = Validator::make($nonExistingData, (new StoreTaskScheduleRequest())->rules());

        $this->assertTrue($validator->fails());

    }

}
