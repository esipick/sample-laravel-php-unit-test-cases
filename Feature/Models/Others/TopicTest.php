<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\Tasks\Task;
use App\Models\Topic;
use App\Models\User;
use Tests\TestCase;

class TopicTest extends TestCase
{
    public function testBelongsToRelationship()
    {
        $customer = Customer::factory()->create();

        $topic = Topic::factory()->for($customer)->create();
        $topic2 = Topic::factory()->for($customer)->create([
            'topicParentID' => $topic->topicID,
        ]);

        $this->assertInstanceOf(Topic::class, $topic);
        $this->assertInstanceOf(Customer::class, $topic->customer);
        $this->assertInstanceOf(Topic::class, $topic2->parent);
    }

    public function testHasManyRelationship()
    {
        $customer = Customer::factory()->create();
        $topic = Topic::factory()->for($customer)->create();

        Topic::factory()->for($customer)->create(['topicParentID' => $topic->topicID]);
        $this->assertInstanceOf(Topic::class, $topic->children[0]);

        $this->assertInstanceOf(Topic::class, $topic->descendants[0]);

        Task::factory()->for($customer)->for($topic)->create();
        $this->assertInstanceOf(Task::class, $topic->tasks[0]);
    }

    public function testCustomerInfoScope()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $this->actingAs($user, 'api');

        $topic = Topic::factory()->create(['customerID' => $user->customerID]);
        $result = Topic::customerInfo()->first();

        $this->assertInstanceOf(Topic::class, $result);
        $this->assertEquals($topic->topicID, $result->topicID);
    }

    public function testActiveScope()
    {
        $customer = Customer::factory()->create();
        $activeTopic = Topic::factory()->for($customer)->create(['topicDeleted' => 0]);
        $inactiveTopic = Topic::factory()->for($customer)->create(['topicDeleted' => 1]);

        $result = Topic::active()->get();

        $this->assertTrue($result->contains($activeTopic));
        $this->assertFalse($result->contains($inactiveTopic));
    }

    public function testGetAllParentsMethod()
    {
        $customer = Customer::factory()->create();

        $parent = Topic::factory()->for($customer)->create();
        $child = Topic::factory()->for($customer)->create(['topicParentID' => $parent->topicID]);

        $allParents = $child->getAllParents();

        $this->assertFalse($allParents->contains($parent));
    }

    public function testGetAllTasksCountMethod()
    {
        $customer = Customer::factory()->create();
        $topic = Topic::factory()->for($customer)->create();

        $task1 = Task::factory()->for($customer)->for($topic)->create();
        $task2 = Task::factory()->for($customer)->for($topic)->create();

        $descendant = Topic::factory()->for($customer)->create(['topicParentID' => $topic->topicID]);
        $task3 = Task::factory()->for($customer)->for($descendant)->create();

        $expectedCount = 3;
        $actualCount = $topic->getAllTasksCount();

        $this->assertEquals($expectedCount, $actualCount);
    }
}
