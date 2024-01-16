<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\CreateTopicRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateTopicRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = new CreateTopicRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $topic = Topic::factory()->for($customer)->create();

        $validData = [
            'topicTitle' => 'ValidTopicTitle',
            'topicParentID' => $topic->topicID,
        ];

        $validator = Validator::make($validData, (new CreateTopicRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationPassesWithMissingOptionalFields()
    {
        $validDataWithoutOptionalFields = [
            'topicTitle' => 'ValidTopicTitle',
        ];

        $validator = Validator::make($validDataWithoutOptionalFields, (new CreateTopicRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'topicTitle' => str_repeat('a', 256), // Exceeds the max length
            'topicParentID' => 'not_an_integer',
        ];

        $validator = Validator::make($invalidData, (new CreateTopicRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
