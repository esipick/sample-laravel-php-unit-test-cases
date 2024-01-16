<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreDocumentRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreDocumentRequest();

        $this->assertTrue($request->authorize());
    }

    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $validData = [
            'documentFileName' => 'example_file',
            'uploadFileName' => 'example_upload',
            'locationID' => $location->locationID,
            'documentScope' => 'local',
        ];

        $validator = Validator::make($validData, (new StoreDocumentRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testDocumentFileNameIsRequired()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        $invalidData = [
            'uploadFileName' => 'example_upload',
            'locationID' => $location->locationID,
            'documentScope' => 'local',
        ];

        $validator = Validator::make($invalidData, (new StoreDocumentRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documentFileName', $validator->errors()->toArray());
    }

    public function testUploadFileNameIsRequired()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);

        $invalidData = [
            'documentFileName' => 'example_file',
            'locationID' => $location->locationID,
            'documentScope' => 'local',
        ];

        $validator = Validator::make($invalidData, (new StoreDocumentRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('uploadFileName', $validator->errors()->toArray());
    }
}
