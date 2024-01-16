<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\GetTaskBoardUsersRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTaskBoardUsersRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrue()
    {
        $request = new GetTaskBoardUsersRequest();
        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $validData = [
            'perPage' => 10,
            'orderBy' => 'asc',
            'orderByField' => 'userFirstName',
            'page' => 1,
        ];

        $validator = Validator::make($validData, (new GetTaskBoardUsersRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'perPage' => 'not_an_integer',
            'orderBy' => 'invalid_order',
            'orderByField' => 'invalid_field',
            'page' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new GetTaskBoardUsersRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
