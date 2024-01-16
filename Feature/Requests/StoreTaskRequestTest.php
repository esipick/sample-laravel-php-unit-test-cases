<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTaskRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreTaskRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $validData = [
            'name' => 'Valid Task Name',
            'locationID' => $location->locationID,
            'isLocalAddedTask' => true,
        ];

        $validator = Validator::make($validData, (new StoreTaskRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testOptionalFieldsCanBeOmitted()
    {
        $dataWithoutOptionalFields = [
            // No 'name', 'locationID', or 'isLocalAddedTask' provided
        ];

        $validator = Validator::make($dataWithoutOptionalFields, (new StoreTaskRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testInvalidDataFailsValidation()
    {
        $invalidData = [
            'name' => str_repeat('a', 91),
            'locationID' => 'invalid',
            'isLocalAddedTask' => 'invalid',
        ];

        $validator = Validator::make($invalidData, (new StoreTaskRequest())->rules());

        $this->assertTrue($validator->fails());
    }

    public function testNonExistingLocationFailsValidation()
    {
        $nonExistingData = [
            'name' => 'Valid Task Name',
            'locationID' => 999, // Assuming this location ID does not exist
            'isLocalAddedTask' => true,
        ];

        $validator = Validator::make($nonExistingData, (new StoreTaskRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
