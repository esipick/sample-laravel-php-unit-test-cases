<?php

namespace Feature\Controllers\Tasks;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Tasks\Task;
use App\Models\Tasks\TasksComment;
use App\Models\User;
use Tests\TestCase;

class TaskCommentControllerTest extends TestCase
{
    public function testTaskCommentsIndex()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $task = Task::factory()->for($customer)->for($location)->for($user)->create();
        $headers = $this->authenticateUser($user);

        $comment = TasksComment::factory()->for($user)->for($task)->create();
        $requestData = [
            'taskID' => $task->taskID,
        ];
        $response = $this->withHeaders($headers)->get("/api/tasks-comments?taskID={$task->taskID}&perPage=10&orderBy=desc&orderByField=updated_at", $requestData);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'taskID' => $comment->taskID,
            'text' => $comment->text,
        ]);
    }

    public function testTaskCommentsIndexDefaultSort()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $task = Task::factory()->for($customer)->for($location)->for($user)->create();
        $headers = $this->authenticateUser($user);

        $comment = TasksComment::factory()->for($user)->for($task)->create();
        $requestData = [
            'taskID' => $task->taskID,
        ];
        $response = $this->withHeaders($headers)->get("/api/tasks-comments?taskID={$task->taskID}", $requestData);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'taskID' => $comment->taskID,
            'text' => $comment->text,
        ]);
    }

    public function testTaskCommentsStore()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $user = User::factory()->for($customer)->create(['notifyEmailAssignmentUser' => true]);
        $task = Task::factory()->for($customer)->for($location)->for($user)->create();
        $headers = $this->authenticateUser($user);

        $commentData = [
            'text' => 'This is a new comment.',
            'taskID' => $task->taskID,
        ];

        $response = $this->withHeaders($headers)->post('/api/tasks-comments', $commentData);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'text' => $commentData['text'],
            'taskID' => $commentData['taskID'],
        ]);

        $this->assertDatabaseHas('tasks_comments', $commentData);
    }
}
