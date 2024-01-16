<?php

namespace Feature\Models\Task;

use App\Enum\TaskItemTypeEnum;
use App\Enum\TaskType;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\DashboardCategoryTaskItem;
use App\Models\DashboardTaskSetCategory;
use App\Models\DashboardTaskSetsItem;
use App\Models\DashboardTaskSetTimeInterval;
use App\Models\DashboardTimeInterval;
use App\Models\Document;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskAuditAssignmentlog;
use App\Models\Tasks\TaskAuditColorlog;
use App\Models\Tasks\TaskCustomReminder;
use App\Models\Tasks\TaskDelay;
use App\Models\Tasks\TaskDocument;
use App\Models\Tasks\TaskFile;
use App\Models\Tasks\TaskItem;
use App\Models\Tasks\TaskReoccur;
use App\Models\Tasks\TasksAssessmentInfo;
use App\Models\Tasks\TasksComment;
use App\Models\Tasks\TasksSchedule;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TaskTest extends TestCase
{
    public function testCanCreateTask()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();

        $this->assertInstanceOf(Task::class, $task);
    }

    public function testCanRetrieveCustomerRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();

        $customer = $task->customer;

        $this->assertInstanceOf(Customer::class, $customer);
    }

    public function testCanRetrieveLocationRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();

        $location = $task->location;

        $this->assertInstanceOf(Location::class, $location);
    }

    public function testCanRetrieveDelaysRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->has(TaskDelay::factory()->for($customer)->for($user)->count(1))->create();

        $delays = $task->delays;

        $this->assertInstanceOf(TaskDelay::class, $delays->first());
    }

    public function testCanRetrieveProfileRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $task = Task::factory()->for($customer)->for($profile)->create();
        $profile = $task->profile;
        $this->assertInstanceOf(Profile::class, $profile);
    }

    public function testCanRetrieveTopicRelationship()
    {
        $customer = Customer::factory()->create();
        $topic = Topic::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($topic)->create();

        $topic = $task->topic;

        $this->assertInstanceOf(Topic::class, $topic);
    }

    public function testCanRetrieveUserRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($user)->create();

        $user = $task->user;

        $this->assertInstanceOf(User::class, $user);
    }

    public function testCanRetrieveCompletedByUserRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->for($user, 'completedByUser')->create();

        $completedByUser = $task->completedByUser;

        $this->assertInstanceOf(User::class, $completedByUser);
    }

    public function testCanRetrieveTaskReoccursRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskReoccur::factory()->for($customer)->count(1))->create();

        $taskReoccurs = $task->taskReoccurs;

        $this->assertInstanceOf(TaskReoccur::class, $taskReoccurs->first());
    }

    public function testCanRetrieveTaskItemsRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskItem::factory()->for($customer)->count(5))->create();

        $taskItems = $task->taskItems;

        $this->assertInstanceOf(TaskItem::class, $taskItems->first());
    }

    public function testCanRetrieveTaskFilesRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskFile::factory()->for($customer)->count(5))->create();

        $taskFiles = $task->taskFiles;

        $this->assertInstanceOf(TaskFile::class, $taskFiles->first());
    }

    public function testCanRetrieveTaskDocumentsRelationship()
    {
        $customer = Customer::factory()->create();
        $document = Document::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->has(TaskDocument::factory()->for($document)->count(5))->create();
        $taskDocuments = $task->taskDocuments;

        $this->assertInstanceOf(TaskDocument::class, $taskDocuments->first());
    }

    public function testCanRetrieveTasksAssessmentInfoRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->has(TasksAssessmentInfo::factory()->for($user)->count(1))->create();

        $tasksAssessmentInfo = $task->tasksAssessmentInfo;

        $this->assertInstanceOf(TasksAssessmentInfo::class, $tasksAssessmentInfo->first());
    }

    public function testCanRetrieveTasksCommentsRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->has(TasksComment::factory()->for($user)->count(1))->create();

        $tasksComments = $task->tasksComments;

        $this->assertInstanceOf(TasksComment::class, $tasksComments->first());
    }

    public function testCanRetrieveTaskCustomRemindersRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $task = Task::factory()->for($customer)->has(TaskCustomReminder::factory()->for($user)->for($profile)->count(1))->create();

        $taskCustomReminders = $task->taskCustomReminders;

        $this->assertInstanceOf(TaskCustomReminder::class, $taskCustomReminders->first());
    }

    public function testCanRetrieveTaskAuditColorlogsRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskAuditColorlog::factory()->for($customer)->count(1))->create();

        $taskAuditColorlogs = $task->taskAuditColorlogs;

        $this->assertInstanceOf(TaskAuditColorlog::class, $taskAuditColorlogs->first());
    }

    public function testCanRetrieveTaskAuditAssignmentlogsRelationship()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->has(TaskAuditAssignmentlog::factory()->for($customer)->count(1))->create();

        $taskAuditAssignmentlogs = $task->taskAuditAssignmentlogs;

        $this->assertInstanceOf(TaskAuditAssignmentlog::class, $taskAuditAssignmentlogs->first());
    }

    public function testCanRetrieveChildrenRelationship()
    {
        $customer = Customer::factory()->create();
        $parentTask = Task::factory()->for($customer)->create();
        $childTask = Task::factory()->for($customer)->create(['taskSetTemplateID' => $parentTask->taskID]);

        $children = $parentTask->children;

        $this->assertInstanceOf(Task::class, $children->first());
    }

    public function testCanRetrieveParentRelationship()
    {
        $customer = Customer::factory()->create();
        $parentTask = Task::factory()->for($customer)->create();
        $childTask = Task::factory()->for($customer)->create(['taskSetTemplateID' => $parentTask->taskID]);

        $parent = $childTask->parent;

        $this->assertInstanceOf(Task::class, $parent);
    }

    public function testCanRetrieveTaskSchedulesRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $reoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        TasksSchedule::factory()->for($customer)->for($task)->for($reoccur)->create();
        $task->refresh();

        $taskSchedules = $task->taskSchedules;

        $this->assertInstanceOf(TasksSchedule::class, $taskSchedules->first());
    }

    public function testCanRetrieveDashboardTaskSetsItemsRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->create();
        $task = Task::factory()->for($customer)->for($location)->for($dashboard)->has(DashboardTaskSetsItem::factory()->for($dashboard)->for($user)->count(1), 'dashboardTaskSetItems')->create();
        $task->refresh();
        $dashboardTaskSetsItems = $task->dashboardTaskSetItems;

        $this->assertInstanceOf(DashboardTaskSetsItem::class, $dashboardTaskSetsItems->first());
    }

    public function testDashboardTasksetsItemRelationship()
    {
        // Create a task
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->create();
        $task = Task::factory()->for($customer)->for($location)->for($dashboard)->create();

        $dashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($user)->create([
            'taskSetID' => $task->taskID,
        ]);

        $retrievedDashboardTaskSetsItem = $task->dashboardTasksetsItem;
        $this->assertTrue($retrievedDashboardTaskSetsItem->is($dashboardTaskSetsItem));
    }

    public function testCanRetrieveDashboardCategoryTaskItemRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $DashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();
        DashboardCategoryTaskItem::factory()->for($DashboardTaskSetsItem)->for($task)->create();

        $task->refresh();
        $dashboardCategoryTaskItem = $task->dashboardCategoryTaskItem;

        $this->assertInstanceOf(DashboardCategoryTaskItem::class, $dashboardCategoryTaskItem);
    }

    public function testCanRetrieveDashboardTaskSetCategoriesRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $DashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();
        DashboardTaskSetCategory::factory()->for($DashboardTaskSetsItem)->for($task)->create();
        $task->refresh();
        $dashboardTaskSetCategories = $task->dashboardTaskSetCategories;

        $this->assertInstanceOf(DashboardTaskSetCategory::class, $dashboardTaskSetCategories->first());
    }

    public function testCanRetrieveDashboardTaskSetTimeIntervalRelationship()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create();
        $dashboard = Dashboard::factory()->for($customer)->for($location)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $dashboardTimeInterval = DashboardTimeInterval::factory()->for($dashboard)->for($customer)->create();
        $DashboardTaskSetsItem = DashboardTaskSetsItem::factory()->for($dashboard)->for($task)->for($user)->create();
        DashboardTaskSetTimeInterval::factory()->for($dashboardTimeInterval, 'timeInterval')->for($DashboardTaskSetsItem)->for($task)->create();
        $task->refresh();
        $dashboardTaskSetTimeInterval = $task->dashboardTaskSetTimeInterval;

        $this->assertInstanceOf(DashboardTaskSetTimeInterval::class, $dashboardTaskSetTimeInterval);
    }

    public function testCanRetrieveDeployedLocationsRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $batchTask = Task::factory()->for($customer)->for($location)->create();
        Task::factory()->for($customer)->for($location)->create(['batchID' => $batchTask->batchID]);
        $batchTask->refresh();

        $deployedLocations = $batchTask->deployedLocations;

        $this->assertInstanceOf(Task::class, $deployedLocations->first());
    }

    public function testCanRetrieveSpawnedTasksRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $templateTask = Task::factory()->for($customer)->for($location)->create();
        Task::factory()->for($customer)->for($location)->create(['templateID' => $templateTask->taskID]);
        $templateTask->refresh();
        $spawnedTasks = $templateTask->spawnedTasks;

        $this->assertInstanceOf(Task::class, $spawnedTasks->first());
    }

    public function testCanRetrieveLatestTaskRelationship()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $batchTask = Task::factory()->for($customer)->for($location)->create();
        Task::factory()->for($customer)->for($location)->create(['batchID' => $batchTask->batchID]);
        $batchTask->refresh();
        $latest = $batchTask->latestTask;

        $this->assertInstanceOf(Task::class, $latest);
    }

    public function testCanGetIsCompletedAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['completedAt' => now()]);

        $this->assertTrue($task->isCompleted);
    }

    public function testCanGetFinalColorAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();

        $this->assertEquals($task->color, $task->finalColor);
    }

    public function testCanGetHasAppendToTitleTaskItemAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $taskItemType = TaskItemTypeEnum::APPENDTOTITLE;
        TaskItem::factory()->for($customer)->create(['taskID' => $task->taskID, 'itemTypeID' => $taskItemType]);

        $taskItems = $task->taskItems;

        $hasAppendToTitle = $taskItems->filter(function ($taskItem) use ($taskItemType) {
            return $taskItem->itemTypeID == $taskItemType->value;
        });

        $this->assertTrue($hasAppendToTitle->count() > 0);
        $this->assertTrue($task->hasAppendToTitleTaskItem);
    }

    public function testReturnsFalseWhenNoAppendToTitleTaskItem()
    {
        // Create a Task with TaskItems that do not match the condition
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $taskItemType = TaskItemTypeEnum::ASSIGNTASK;
        TaskItem::factory()->for($customer)->create(['taskID' => $task->taskID, 'itemTypeID' => $taskItemType]);

        $this->assertFalse($task->hasAppendToTitleTaskItem);
    }

    public function testCanGetIsSpawnedAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => now()]);

        $this->assertTrue($task->isSpawned);
    }

    public function testCanGetIsNativeAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['isLocalAddedTask' => true]);

        $this->assertTrue($task->isNative);
    }

    public function testCanGetIsGlobalAttribute()
    {
        $customer = Customer::factory()->create();
        $task = Task::factory()->for($customer)->create();

        $this->assertTrue($task->isGlobal);
    }

    public function testCanGetIsDeployedAttribute()
    {
        $customer = Customer::factory()->create();
        $globalTask = Task::factory()->for($customer)->create();
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->for($globalTask, 'globalTask')->create();

        $this->assertTrue($task->isDeployed);
    }

    public function testCanGetIsOpenAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => now()]);

        $this->assertTrue($task->isOpen);
    }

    public function testIsNotOpenAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => now(), 'completedAt' => now()]);

        // Access the isOpen attribute
        $isOpen = $task->isOpen;

        $this->assertFalse($isOpen);
    }

    public function testCanGetHasScheduledAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $profile = Profile::factory()->for($location)->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $reoccur = TaskReoccur::factory()->for($customer)->for($task)->create();
        TasksSchedule::factory()->for($reoccur)->for($user)->for($profile)->for($customer)->create(['taskID' => $task->taskID]);

        $this->assertTrue($task->hasScheduled);
    }

    public function testHasNoScheduledAttribute()
    {
        // Create a Task model without related task schedules for testing
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();

        // Access the hasScheduled attribute
        $hasScheduled = $task->hasScheduled;

        $this->assertFalse($hasScheduled);
    }

    public function testGetDueAtStatusDisplayAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($location)->for($customer)->create(['dueAt' => now()]);

        $this->assertEquals($task->getDueAtStatusStringForTask($task), $task->getDueAtStatusDisplayAttribute());
    }

    public function testScopeIsTaskSet()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($location)->for($customer)->create(['isTaskSet' => true]);

        // Create another user with a different value for the "isTaskSet" attribute
        $otherTask = Task::factory()->for($location)->for($customer)->create(['isTaskSet' => false]);

        // Use the scope to query for users where "isTaskSet" is 1
        $tasks = Task::isTaskSet()->get();
        // Assert that the result contains the user with "isTaskSet" set to 1
        $this->assertTrue($tasks->contains($task));

        // Assert that the result does not contain the user with "isTaskSet" set to 0
        $this->assertFalse($tasks->contains($otherTask));
    }

    public function testScopeIsTask()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($location)->for($customer)->create(['isTaskSet' => false]);

        // Create another user with a different value for the "isTaskSet" attribute
        $otherTask = Task::factory()->for($location)->for($customer)->create(['isTaskSet' => true]);

        // Use the scope to query for users where "isTaskSet" is 1
        $tasks = Task::isTask()->get();
        // Assert that the result contains the user with "isTaskSet" set to 1
        $this->assertTrue($tasks->contains($task));

        // Assert that the result does not contain the user with "isTaskSet" set to 0
        $this->assertFalse($tasks->contains($otherTask));
    }

    public function testScopeAssessmentTaskSets()
    {
        $customer = Customer::factory()->create();
        // Create an assessment task set meeting the criteria
        $assessmentTask = Task::factory()->for($customer)->create([
            'isTaskSet' => 1,
            'type' => TaskType::ASSESSMENT,
            'templateID' => null,
            'dashboardID' => null,
            'locationID' => null,
        ]);

        // Create a task with the same attributes as assessment but not matching the type
        $otherTask = Task::factory()->for($customer)->create([
            'isTaskSet' => 1,
            'type' => TaskType::RECURRING,
            'templateID' => null,
            'dashboardID' => null,
            'locationID' => null,
        ]);

        // Use the scope to query for assessment task sets
        $result = Task::assessmentTaskSets()->get();

        // Assert that the result contains the assessment task
        $this->assertTrue($result->contains($assessmentTask));

        // Assert that the result does not contain the other task
        $this->assertFalse($result->contains($otherTask));
    }

    public function testScopeTaskCompleted()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $completedTask = Task::factory()->for($location)->for($customer)->create(['completedAt' => now()]);
        $incompleteTask = Task::factory()->for($location)->for($customer)->create(['completedAt' => null]);

        $result = Task::taskCompleted()->get();

        $this->assertTrue($result->contains($completedTask));
        $this->assertFalse($result->contains($incompleteTask));
    }

    public function testScopeIsParent()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        Task::factory()->for($location)->for($customer)->count(5)->create();
        $tasks = Task::isParent()->get();

        // Assert that tasks are filtered correctly
        $this->assertNull($tasks[0]->taskSetTemplateID);
        $this->assertNull($tasks[0]->templateID);
    }

    public function testScopeIsEvent()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        Task::factory()->for($location)->for($customer)->count(5)->create(['type' => TaskType::EVENT->value]);
        $tasks = Task::isEvent()->get();

        // Assert that tasks are filtered correctly
        $this->assertEquals(TaskType::EVENT, $tasks[0]->type);
    }

    public function testScopeIsChild()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $parentTaskSet = Task::factory()->for($customer)->create(['isTaskSet' => 1]);
        $globalTask = Task::factory()->for($customer)->create();
        Task::factory()->for($location)->for($customer)->count(5)->create(['taskSetTemplateID' => $parentTaskSet->taskID, 'templateID' => $globalTask->taskID]);
        $tasks = Task::isChild()->get();

        // Assert that tasks are filtered correctly
        $this->assertNotNull($tasks[0]->taskSetTemplateID);
        $this->assertNotNull($tasks[0]->templateID);
    }

    public function testScopeIsCompleted()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        Task::factory()->for($location)->for($customer)->count(5)->create(['completedAt' => now()]);
        $tasks = Task::isCompleted()->get();

        // Assert that tasks are filtered correctly
        $this->assertNotNull($tasks[0]->completedAt);
    }

    public function testGetAssociatedUsers()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($user)->create();
        $associatedUsers = $task->getAssociatedUsers();
        $this->assertTrue($associatedUsers->contains(function ($item, $key) use ($user) {
            return $item->userID === $user->userID;
        }));
    }

    public function testDueDateDisplayAttribute()
    {
        $dueDate = now()->addDays(3); // Set a due date in the future
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($location)->for($customer)->for($user)->create(['dueAt' => $dueDate]);

        // Access the dueDateDisplay attribute
        $dueDateDisplay = $task->dueDateDisplay;

        // Assert that the dueDateDisplay attribute is formatted as expected
        $this->assertEquals($dueDate->format('n-j-Y \a\t g:i A T'), $dueDateDisplay);
    }

    public function testDueDateDisplayAttributeIsNull()
    {
        // Create a Task model without a due date for testing
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($location)->for($customer)->for($user)->create(['dueAt' => null]);

        // Access the dueDateDisplay attribute
        $dueDateDisplay = $task->dueDateDisplay;

        // Assert that the dueDateDisplay attribute is null
        $this->assertNull($dueDateDisplay);
    }

    public function testCustomerScope()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($location)->for($customer)->for($user)->create(['dueAt' => null]);

        // Log in the user
        Auth::login($user);

        // Use the scope and retrieve the results
        $result = Task::customerScope()->get();

        // Assert that the result contains the test task instance
        $this->assertTrue($result->contains($task));
    }

    public function testDelayDateDisplayAttribute()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $user = User::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->has(TaskDelay::factory()->for($customer)->for($user)->count(1))->create();
        $result = $task->getDelayDateDisplayAttribute();
        $this->assertNotNull($result);
    }

    public function testDelayDateDisplayAttributeIsNull()
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create();
        $result = $task->getDelayDateDisplayAttribute();
        $this->assertNull($result);
    }
}
