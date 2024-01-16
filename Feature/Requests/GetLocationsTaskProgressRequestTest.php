<?php

namespace Tests\Feature\Requests;

use App\Enum\CredEnum;
use App\Http\Requests\GetLocationsTaskProgressRequest;
use App\Models\Cred;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\ProfileCred;
use App\Models\Security;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetLocationsTaskProgressRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthorizeReturnsTrueWhenUserCanViewDashboard()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();

        $request = new GetLocationsTaskProgressRequest();
        $this->actingAs($user);

        $this->assertTrue($request->authorize());
    }

    public function testAuthorizeReturnsFalseWhenUserCannotViewDashboard()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $creds = Cred::all();
        $creds->each(function ($cred) use ($profile) {
            if (in_array($cred->credID, [CredEnum::VIEW_DASHBOARD->value])) {
                ProfileCred::factory()->for($profile)->for($cred)->create(['profileCredStatus' => 0]);
            } else {
                ProfileCred::factory()->for($profile)->for($cred)->create();
            }
        });

        $request = new GetLocationsTaskProgressRequest();
        $user->refresh();
        $this->actingAs($user);
        $this->assertFalse($request->authorize());
    }

    public function testAuthorizeReturnsFalseWhenNoUser()
    {
        $request = new GetLocationsTaskProgressRequest();

        $this->assertFalse($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $validData = [
            'assignedLocations' => true,
        ];

        $validator = Validator::make($validData, (new GetLocationsTaskProgressRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRulesValidationFailsWithInvalidData()
    {
        $invalidData = [
            'assignedLocations' => 'not_a_boolean',
        ];

        $validator = Validator::make($invalidData, (new GetLocationsTaskProgressRequest())->rules());

        $this->assertTrue($validator->fails());
    }
}
