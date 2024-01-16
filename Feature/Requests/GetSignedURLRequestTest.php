<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetSignedURLRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetSignedURLRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrue()
    {
        $request = new GetSignedURLRequest();
        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $validData = [
            'fileName' => 'example.pdf',
        ];

        $validator = Validator::make($validData, (new GetSignedURLRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'fileName' => 123,
        ];

        $validator = Validator::make($invalidData, (new GetSignedURLRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
