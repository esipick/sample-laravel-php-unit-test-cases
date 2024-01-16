<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetLegalRefRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetLegalRefRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthorizeAlwaysReturnsTrue()
    {
        $request = new GetLegalRefRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $validData = [
            'orderBy' => 'asc',
            'orderByField' => 'legalRefID',
            'search' => 'example',
            'perPage' => 10,
            'page' => 1,
        ];

        $validator = Validator::make($validData, (new GetLegalRefRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'orderBy' => 'invalid_order',
            'orderByField' => 'invalid_field',
            'search' => 123,
            'perPage' => 'not_an_integer',
            'page' => 0,
        ];

        $validator = Validator::make($invalidData, (new GetLegalRefRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
