<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetTopicsRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTopicsRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrue()
    {
        $request = new GetTopicsRequest();
        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $validData = [
            'search' => 'topic',
            'perPage' => 10,
            'orderBy' => 'asc',
            'orderByField' => 'topicID',
            'page' => 1,
        ];

        $validator = Validator::make($validData, (new GetTopicsRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'search' => 123, // should be a string
            'perPage' => 'not_an_integer', // should be an integer
            'orderBy' => 'invalid_order', // should be 'asc' or 'desc'
            'orderByField' => 'invalid_field', // invalid field
            'page' => 'not_an_integer', // should be an integer
        ];

        $validator = Validator::make($invalidData, (new GetTopicsRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
