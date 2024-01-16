<?php

namespace Tests\Feature\Requests;

use App\Enum\CredEnum;
use App\Enum\UserTypeEnum;
use App\Http\Requests\LocationDashboardRequest;
use App\Models\Cred;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\ProfileCred;
use App\Models\Security;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksAssessmentInfo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LocationDashboardRequestTest extends TestCase
{
    public function testAuthorizeReturnsFalseWhenUserIsNotAuthorized()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        Auth::shouldReceive('user')->andReturn(null);

        $request = new LocationDashboardRequest(['locationID' => $location->locationID]);

        $this->assertFalse($request->authorize());
    }
    public function testAuthorizeReturnsTrueWhenUserIsAuthorized()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        TasksAssessmentInfo::factory()->for($task)->for($user)->create();
        Auth::shouldReceive('user')->andReturn($user);

        $request = new LocationDashboardRequest(['locationID' => $location->locationID]);

        $this->assertTrue($request->authorize());
    }
    public function testAuthorizeReturnsTrueWhenUserIsAuthorizedInvalidCustomer()
    {
        $customer = Customer::factory()->create();
        $customer1 = Customer::factory()->create();
        $location = Location::factory()->for($customer1)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        TasksAssessmentInfo::factory()->for($task)->for($user)->create();
        Auth::shouldReceive('user')->andReturn($user);

        $request = new LocationDashboardRequest(['locationID' => $location->locationID]);

        $this->assertFalse($request->authorize());
    }

    public function testAuthorizeReturnsTrueForAdminPersonal()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create(['userType' => UserTypeEnum::SuperAdmin]);

        Auth::shouldReceive('user')->andReturn($user);

        $request = new LocationDashboardRequest();

        $this->assertTrue($request->authorize());
    }

    public function testAuthorizeReturnsTrueForReadOnly()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $creds = Cred::all();
        $creds->each(function ($cred) use ($profile) {
            if (in_array($cred->credID, [CredEnum::AD_ADMIN_ALL->value, CredEnum::AD_ADMIN_PERSONAL->value])) {
                ProfileCred::factory()->for($profile)->for($cred)->create(['profileCredStatus' => 0]);
            } else {
                ProfileCred::factory()->for($profile)->for($cred)->create();
            }
        });

        Auth::shouldReceive('user')->andReturn($user);

        $request = new LocationDashboardRequest();

        $this->assertTrue($request->authorize());
    }

    public function testAuthorizeReturnsTrueForSelf()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $creds = Cred::all();
        $creds->each(function ($cred) use ($profile) {
            if (in_array($cred->credID, [CredEnum::AD_ADMIN_ALL->value])) {
                ProfileCred::factory()->for($profile)->for($cred)->create(['profileCredStatus' => 0]);
            } else {
                ProfileCred::factory()->for($profile)->for($cred)->create();
            }
        });

        Auth::shouldReceive('user')->andReturn($user);

        $request = new LocationDashboardRequest();

        $this->assertTrue($request->authorize());
    }


    public function testAuthorizeReturnsTrueForViewDashboard()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $creds = Cred::all();
        $creds->each(function ($cred) use ($profile) {
            if (in_array($cred->credID, [CredEnum::AD_ADMIN_ALL->value, CredEnum::AD_ADMIN_PERSONAL->value, CredEnum::AD_READ_ONLY->value])) {
                ProfileCred::factory()->for($profile)->for($cred)->create(['profileCredStatus' => 0]);
            } else {
                ProfileCred::factory()->for($profile)->for($cred)->create();
            }
        });

        Auth::shouldReceive('user')->andReturn($user);

        $request = new LocationDashboardRequest();

        $this->assertTrue($request->authorize());
    }
    public function testAuthorizeReturnsTrueForViewDashboardNone()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create(['userType' => null]);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();
        $creds = Cred::all();
        $creds->each(function ($cred) use ($profile) {
            if (in_array($cred->credID, [CredEnum::AD_ADMIN_ALL->value, CredEnum::AD_ADMIN_PERSONAL->value, CredEnum::AD_READ_ONLY->value, CredEnum::VIEW_DASHBOARD->value])) {
                ProfileCred::factory()->for($profile)->for($cred)->create(['profileCredStatus' => 0]);
            } else {
                ProfileCred::factory()->for($profile)->for($cred)->create();
            }
        });

        Auth::shouldReceive('user')->andReturn($user);

        $request = new LocationDashboardRequest();

        $this->assertFalse($request->authorize());
    }

    public function testRulesValidationPassesWithValidData()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $validData = [
            'locationID' => $location->locationID,
            'page' => 1,
            'perPage' => 10,
            'search' => 'Example',
            'status' => 'Active',
            'orderBy' => 'asc',
            'orderByField' => 'dashboardName',
            'globalAssessmentDashboards' => true,
        ];

        $validator = Validator::make($validData, (new LocationDashboardRequest())->rules());

        $this->assertFalse($validator->fails());
    }

}
