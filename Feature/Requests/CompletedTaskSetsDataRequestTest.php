<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\CompletedTaskSetsDataRequest;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CompletedTaskSetsDataRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(true);

        $request = new CompletedTaskSetsDataRequest();

        $this->assertTrue($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $validData = [
            'assignedTo' => $user->userID,
            'completedBy' => $user->userID,
            'dueDateStart' => '2023-01-01',
            'dueDateEnd' => '2023-01-10',
            'completedDateStart' => '2023-01-05',
            'completedDateEnd' => '2023-01-15',
            'search' => 'ValidSearch',
            'locationID' => $location->locationID,
            'orderBy' => 'asc',
            'orderByField' => 'taskID',
        ];

        $validator = Validator::make($validData, (new CompletedTaskSetsDataRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'assignedTo' => 'not_an_integer',
            'completedBy' => 'not_an_integer',
            'dueDateStart' => 'invalid_date_format',
            'dueDateEnd' => '2023-01-01', // Before completedDateStart
            'completedDateStart' => 'invalid_date_format',
            'completedDateEnd' => '2023-01-01', // Before completedDateStart
            'search' => null, // Missing required field
            'locationID' => 'not_an_integer',
            'orderBy' => 'invalid_order',
            'orderByField' => 'invalid_field',
        ];

        $validator = Validator::make($invalidData, (new CompletedTaskSetsDataRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
