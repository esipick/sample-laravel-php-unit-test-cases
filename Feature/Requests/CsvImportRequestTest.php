<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\CsvImportRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CsvImportRequestTest extends TestCase
{
    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);

        $validData = [
            'TaskId' => $task->taskID,
            'TaskName' => 'ValidTaskName',
            'IsTaskSet' => true,
        ];

        $validator = Validator::make($validData, (new CsvImportRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationPassesWithMissingNullableField()
    {
        $validDataWithoutNullableField = [
            'TaskName' => 'ValidTaskName',
            'IsTaskSet' => true,
        ];

        $validator = Validator::make($validDataWithoutNullableField, (new CsvImportRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'TaskId' => 'not_an_integer',
            'TaskName' => str_repeat('a', 256), // Exceeds the max length
            'IsTaskSet' => 'not_a_boolean',
        ];

        $validator = Validator::make($invalidData, (new CsvImportRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
