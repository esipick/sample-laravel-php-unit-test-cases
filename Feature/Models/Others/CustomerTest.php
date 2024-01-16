<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Location;
use App\Models\Profile;
use App\Models\SocialiteClient;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskDelay;
use App\Models\Tasks\TaskItem;
use App\Models\Tasks\TaskReoccur;
use App\Models\Tasks\TaskReoccurType;
use App\Models\Tasks\TasksReportCustom;
use App\Models\Topic;
use App\Models\User;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    public function testCreateCustomer()
    {
        $customer = Customer::factory()->create();

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertDatabaseHas('customers', ['customerID' => $customer->customerID]);
    }

    public function testIsActiveScope()
    {
        Customer::factory()->create(['customerActive' => 1]);

        $activeCustomers = Customer::isActive()->get();

        $this->assertGreaterThanOrEqual(1, $activeCustomers->count());
    }

    public function testUsersRelationship()
    {
        $customer = Customer::factory()->create();
        User::factory()->for($customer)->create();
        $this->assertInstanceOf(User::class, $customer->users->first());
    }

    public function testProfilesRelationship()
    {
        $customer = Customer::factory()->create();
        Profile::factory()->for($customer)->create();
        $this->assertInstanceOf(Profile::class, $customer->profiles->first());
    }

    public function testTaskReoccursRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $reoccurType = TaskReoccurType::find(1);

        TaskReoccur::create([
            'startDate' => '2023-10-25',
            'field1' => 1,
            'taskID' => $task->taskID,
            'customerID' => $customer->customerID,
            'type' => $reoccurType->reoccurTypeID,
        ]);

        $this->assertInstanceOf(TaskReoccur::class, $customer->taskReoccurs->first());
    }

    public function testTasksReportCustomRelationship()
    {
        $customer = Customer::factory()->create();
        TasksReportCustom::factory()->for($customer)->create();
        $this->assertInstanceOf(TasksReportCustom::class, $customer->tasksReportCustom->first());
    }

    public function testTaskItemsRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();
        TaskItem::factory()->for($customer)->for($task)->create();
        $this->assertInstanceOf(TaskItem::class, $customer->taskItems->first());
    }

    public function testTaskDelaysRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        TaskDelay::factory()->for($user)->for($customer)->for($task)->create();
        $this->assertInstanceOf(TaskDelay::class, $customer->taskDelays->first());
    }

    public function testLocationsRelationship()
    {
        $customer = Customer::factory()->create();
        Location::factory()->for($customer)->create();
        $this->assertInstanceOf(Location::class, $customer->locations->first());
    }

    public function testTasksRelationship()
    {
        $customer = Customer::factory()->create();
        Task::factory()->for($customer)->create();
        $this->assertInstanceOf(Task::class, $customer->tasks->first());
    }

    public function testDocumentsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        Document::factory()->for($customer)->for($location)->create();
        $this->assertInstanceOf(Document::class, $customer->documents->first());
    }

    public function testTopicsRelationship()
    {
        $customer = Customer::factory()->create();
        Topic::factory()->for($customer)->create();
        $this->assertInstanceOf(Topic::class, $customer->topics->first());
    }

    public function testSocialiteClientRelationship()
    {
        $customer = Customer::factory()->create();
        SocialiteClient::factory()->for($customer)->create();
        $this->assertInstanceOf(SocialiteClient::class, $customer->socialiteClient);
    }
}
