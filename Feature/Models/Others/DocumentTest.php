<?php

namespace Feature\Models\Others;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskDocument;
use App\Models\User;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    public function testCreateDocument()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $document = Document::factory()->for($customer)->for($location)->create();

        $this->assertInstanceOf(Document::class, $document);
        $this->assertDatabaseHas('document', ['documentID' => $document->documentID]);
    }

    public function testUploadedByUserRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $document = Document::factory()->for($customer)->for($location)->for($user, 'uploadedByUser')->create();
        $this->assertInstanceOf(User::class, $document->uploadedByUser);
    }

    public function testLocationRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $document = Document::factory()->for($customer)->for($location)->for($user, 'uploadedByUser')->create();

        $this->assertInstanceOf(Location::class, $document->location);
    }

    public function testTaskDocumentsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        $document = Document::factory()->for($customer)->for($location)->for($user, 'uploadedByUser')->create();
        TaskDocument::factory()->for($task)->for($document)->create();

        $this->assertInstanceOf(TaskDocument::class, $document->taskDocuments->first());
    }

    public function testCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        $document = Document::factory()->for($customer)->for($location)->for($user, 'uploadedByUser')->create();

        $this->assertInstanceOf(Customer::class, $document->customer);
    }
}
