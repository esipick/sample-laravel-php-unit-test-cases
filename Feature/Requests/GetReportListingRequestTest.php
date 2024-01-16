<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetReportListingRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetReportListingRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrue()
    {
        $request = new GetReportListingRequest();
        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $validData = [
            'search' => 'example',
            'perPage' => 10,
            'orderBy' => 'asc',
            'orderByField' => 'name',
            'page' => 1,
        ];

        $validator = Validator::make($validData, (new GetReportListingRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'search' => 123,
            'perPage' => 'not_an_integer',
            'orderBy' => 'invalid_order',
            'orderByField' => 'invalid_field',
            'page' => 0,
        ];

        $validator = Validator::make($invalidData, (new GetReportListingRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
