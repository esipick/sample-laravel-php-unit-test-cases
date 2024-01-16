<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskCustomReminder;
use App\Models\User;
use Tests\TestCase;

class TaskCustomReminderTest extends TestCase
{
    public function testCreateTaskCustomReminder()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $reminder = TaskCustomReminder::factory()->for($task)->for($profile)->for($user)->create();

        $this->assertInstanceOf(TaskCustomReminder::class, $reminder);
    }

    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $reminder = TaskCustomReminder::factory()->for($task)->for($profile)->for($user)->create();
        $this->assertInstanceOf(Task::class, $reminder->task);
    }

    public function testUserRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $reminder = TaskCustomReminder::factory()->for($task)->for($profile)->for($user)->create();
        $this->assertInstanceOf(User::class, $reminder->user);
    }

    public function testProfileRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $reminder = TaskCustomReminder::factory()->for($task)->for($profile)->for($user)->create();
        $this->assertInstanceOf(Profile::class, $reminder->profile);
    }

    public function testFillableFields()
    {
        $reminder = new TaskCustomReminder;
        $fillable = ['daysBeforeDue', 'userID', 'profileID', 'taskID'];
        $notFillable = array_diff($reminder->getFillable(), $fillable);

        $this->assertEquals($fillable, $reminder->getFillable());
        $this->assertEmpty($notFillable);
    }
}
