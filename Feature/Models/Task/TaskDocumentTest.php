<?php

namespace Feature\Models\Task;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskDocument;
use Tests\TestCase;

class TaskDocumentTest extends TestCase
{
    /**
     * Test creating a TaskDocument.
     */
    public function testCreateTaskDocument()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        $document = Document::factory()->for($customer)->for($location)->create();

        $taskDocument = TaskDocument::create([
            'taskID' => $task->taskID,
            'documentID' => $document->documentID,
        ]);

        $this->assertInstanceOf(TaskDocument::class, $taskDocument);
        $this->assertEquals($task->taskID, $taskDocument->taskID);
        $this->assertEquals($document->documentID, $taskDocument->documentID);
    }

    /**
     * Test updating a TaskDocument's attributes.
     */
    public function testUpdateTaskDocument()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        $document = Document::factory()->for($customer)->for($location)->create();

        $taskDocument = TaskDocument::create([
            'taskID' => $task->taskID,
            'documentID' => $document->documentID,
        ]);

        $newTaskID = $task->taskID;
        $newDocumentID = $document->documentID;

        $taskDocument->update([
            'taskID' => $newTaskID,
            'documentID' => $newDocumentID,
        ]);

        $updatedTaskDocument = TaskDocument::find($taskDocument->taskDocumentsID);

        $this->assertEquals($newTaskID, $updatedTaskDocument->taskID);
        $this->assertEquals($newDocumentID, $updatedTaskDocument->documentID);
    }

    /**
     * Test deleting a TaskDocument.
     */
    public function testDeleteTaskDocument()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        $document = Document::factory()->for($customer)->for($location)->create();

        $taskDocument = TaskDocument::create([
            'taskID' => $task->taskID,
            'documentID' => $document->documentID,
        ]);

        $this->assertTrue($taskDocument->delete());
        $this->assertNull(TaskDocument::find($taskDocument->taskDocumentsID));
    }

    /**
     * Test the task relationship.
     */
    public function testTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        $document = Document::factory()->for($customer)->for($location)->create();

        $taskDocument = TaskDocument::create([
            'taskID' => $task->taskID,
            'documentID' => $document->documentID,
        ]);

        $this->assertInstanceOf(Task::class, $taskDocument->task);
        $this->assertEquals($task->taskID, $taskDocument->task->taskID);
    }

    /**
     * Test the document relationship.
     */
    public function testDocumentRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->create();
        $document = Document::factory()->for($customer)->for($location)->create();

        $taskDocument = TaskDocument::create([
            'taskID' => $task->taskID,
            'documentID' => $document->documentID,
        ]);

        $this->assertInstanceOf(Document::class, $taskDocument->document);
        $this->assertEquals($document->documentID, $taskDocument->document->documentID);
    }
}
