<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\CreateLocationRequest;
use App\Models\Customer;
use App\Models\Timezone;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateLocationRequestTest extends TestCase
{
    public function testAuthorizeAlwaysReturnsTrue()
    {
        $request = new CreateLocationRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $timezoneName = 'America/New_York';
        $timezone = Timezone::factory()->create(['name' => $timezoneName]);
        $validData = [
            'locationName' => 'ValidLocationName',
            'timezoneID' => $timezone->timezoneID,
        ];

        $validator = Validator::make($validData, (new CreateLocationRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationPassesWithMissingOptionalFields()
    {
        $validDataWithoutOptionalFields = [];

        $validator = Validator::make($validDataWithoutOptionalFields, (new CreateLocationRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'locationName' => str_repeat('a', 91), // Exceeds the max length
            'timezoneID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new CreateLocationRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
