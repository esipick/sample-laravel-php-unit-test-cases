<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreLegalRefRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreLegalRefRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreLegalRefRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $validData = [
            'name' => 'Example Legal Reference',
            'link' => 'http://example.com/legal-ref',
        ];

        $validator = Validator::make($validData, (new StoreLegalRefRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testNameIsOptionalButShouldBeStringIfPresent()
    {
        $validData = [
            'link' => 'http://example.com/legal-ref',
        ];

        $validator = Validator::make($validData, (new StoreLegalRefRequest())->rules());

        $this->assertFalse($validator->fails());
    }
}
