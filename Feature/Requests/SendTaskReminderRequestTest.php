<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\SendTaskReminderRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class SendTaskReminderRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new SendTaskReminderRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $validData = [
            'message' => 'Valid message content.',
            'subject' => 'Valid subject',
            'to' => ['user1@example.com', 'user2@example.com'],
        ];

        $validator = Validator::make($validData, (new SendTaskReminderRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testMessageIsRequired()
    {
        $invalidData = [
            'subject' => 'Valid subject',
            'to' => ['user1@example.com'],
        ];

        $validator = Validator::make($invalidData, (new SendTaskReminderRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('message', $validator->errors()->toArray());
    }

    public function testSubjectIsRequired()
    {
        $invalidData = [
            'message' => 'Valid message content.',
            'to' => ['user1@example.com'],
        ];

        $validator = Validator::make($invalidData, (new SendTaskReminderRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('subject', $validator->errors()->toArray());
    }

    public function testToIsRequired()
    {
        $invalidData = [
            'message' => 'Valid message content.',
            'subject' => 'Valid subject',
        ];

        $validator = Validator::make($invalidData, (new SendTaskReminderRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('to', $validator->errors()->toArray());
    }

    public function testToMustBeAnArray()
    {
        $invalidData = [
            'message' => 'Valid message content.',
            'subject' => 'Valid subject',
            'to' => 'invalid@example.com',
        ];

        $validator = Validator::make($invalidData, (new SendTaskReminderRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('to', $validator->errors()->toArray());
    }
}
