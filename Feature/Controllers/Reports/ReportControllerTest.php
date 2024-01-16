<?php

namespace Tests\Feature\Controllers;

use App\Enum\TaskItemTypeEnum;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskItem;
use App\Models\Tasks\TaskItemOption;
use App\Models\Tasks\TaskItemType;
use App\Models\User;
use App\Models\Catalog;
use Tests\TestCase;
use App\Models\Tasks\TasksReportCustom;
use App\Models\Tasks\TasksReportCustomItem;

class ReportControllerTest extends TestCase
{
    public function testIndexFunctionCasePerPage()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        Report::factory()->times(rand(10, 15))->for($customer)->create();
        
        $requestData = [
            'perPage' => rand(2, 6),
        ];

        $apiURL = '/api/reports?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();

        $responseArray = collect($response->json('reports'))->count();

        $this->assertEquals($requestData['perPage'], $responseArray);
    }

    public function testIndexFunctionCaseSearch()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $search = 'abcdefghij';
        $report1 = Report::factory()->times(rand(1, 5))->for($customer)->create([
            'name' => $search.\Str::random(5),
        ]);
        
        $report2 = Report::factory()->times(rand(1, 5))->for($customer)->create([
            'name' => \Str::random(25),
        ]);

        $report3 = Report::factory()->times(rand(1, 5))->for($customer)->create();

        $requestData = [
            'search' => $search,
        ];

        $apiURL = '/api/reports?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();

        $requestArray = $report1->pluck('id')->toArray();
        $responseArray = collect($response->json('reports'))->pluck('id')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testIndexFunctionCaseOrderBy()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $reports = Report::factory()->times(rand(10, 15))->for($customer)->create();

        $requestData = [
            'orderBy' => 'asc',
            'orderByField' => 'name',
        ];

        $apiURL = '/api/reports?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();

        $requestArray = collect($reports)->sortBy('name')->pluck('id')->toArray();
        $responseArray = collect($response->json('reports'))->pluck('id')->toArray();

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testSectionsCatalogFunctionCasePerPage()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        Catalog::factory()->times(rand(10, 15))->create();
        
        $requestData = [
            'perPage' => rand(2, 6),
        ];

        $apiURL = '/api/reports/sections?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();

        $responseArray = collect($response->json('catalog'))->count();
        
        $this->assertEquals($requestData['perPage'], $responseArray);
    }

    public function testSectionsCatalogFunctionCaseSearch()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $search = 'abcdefghij';
        $catalog1[] = Catalog::factory()->create(['name' => $search.\Str::random(5) ]);
        $catalog1[] = Catalog::factory()->create(['name' => $search.\Str::random(5) ]);
        $catalog1[] = Catalog::factory()->create(['name' => $search.\Str::random(5) ]);
        $catalog1[] = Catalog::factory()->create(['name' => $search.\Str::random(5) ]);
        $catalog1[] = Catalog::factory()->create(['name' => $search.\Str::random(5) ]);
        
        $catalog2[] = Catalog::factory()->create(['name' => \Str::random(15)]);
        $catalog2[] = Catalog::factory()->create(['name' => \Str::random(15)]);
        $catalog2[] = Catalog::factory()->create(['name' => \Str::random(15)]);
        $catalog2[] = Catalog::factory()->create(['name' => \Str::random(15)]);
        $catalog2[] = Catalog::factory()->create(['name' => \Str::random(15)]);

        $catalog3[] = Catalog::factory()->create();
        $catalog3[] = Catalog::factory()->create();
        $catalog3[] = Catalog::factory()->create();
        $catalog3[] = Catalog::factory()->create();
        $catalog3[] = Catalog::factory()->create();

        $requestData = [
            'search' => $search,
        ];

        $apiURL = '/api/reports/sections?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();

        $requestArray = collect($catalog1)->pluck('name')->toArray();
        $responseArray = collect($response->json('catalog'))->pluck('name')->toArray();

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testSectionsCatalogFunctionCaseOrderBy()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $catalog = Catalog::all();

        $requestData = [
            'orderBy' => 'asc',
            'orderByField' => 'name',
        ];

        $apiURL = '/api/reports/sections?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();

        $requestArray = collect(Catalog::all())->pluck('name')->toArray();;
        $responseArray = collect($response->json('catalog'))->pluck('name')->toArray();

        sort($requestArray);
        
        $this->assertEquals($requestArray, $responseArray);
    }

    public function testStoreFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $requestData = [
            'name' => \Str::random(10),
            'description' => \Str::random(30),
            'sections' => [
                [
                    'name' => \Str::random(10),
                    'sectionIndex' => "1",
                    'filterCategories' => [
                        [
                            "name" => \Str::random(10),
                            "filterTypes" => [
                                [
                                    "name" => \Str::random(10),
                                ]
                            ]
                        ]
                    ],
                    'filters' => [
                        [
                            'filterTypeIndex' => 0,
                            'filterCategoryIndex' => 0,
                            'filterConfigs' => [\Str::random(15)] 
                        ]
                    ]
                ],
            ]
        ];

        $apiURL = '/api/reports';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure();

        $this->assertDatabaseHas('cr_report', [
            'name' => $requestData['name'],
            'description' => $requestData['description'],
        ]);

        $this->assertDatabaseHas('cr_section', [
            'sectionIndex' => $requestData['sections'][0]['sectionIndex'],
            'catalog' => $requestData['sections'][0]['name'],
        ]);

        $this->assertDatabaseHas('cr_filter_config', [
            'configValue' => $requestData['sections'][0]['filters'][0]['filterConfigs'],
        ]);
    }

    public function testStoreFunctionException()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $requestData = [
            'name' => 'filter name',
            'description' => 'filter description',
            'sections' => [
                [
                    'name' => 'section name',
                    'sectionIndex' => "1",
                ],
            ]
        ];

        $apiURL = '/api/reports';
        $response = $this->withHeaders($headers)->post($apiURL, $requestData);

        $response->assertStatus(422);
    }

    public function testShowFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $report = Report::factory()->for($customer)->create([
            'name' => \Str::random(25)
        ]);

        $apiURL = '/api/reports/'.$report->id;
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure(['report']);

        $requestArray = $report->name;
        $responseArray = $response->json('report.name');

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testUpdateFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $report = Report::factory()->for($customer)->create([
            'name' => \Str::random(25)
        ]);

        $requestData = [
            'id' => $report->id,
            'name' => \Str::random(10),
            'description' => \Str::random(30),
            'sections' => [
                [
                    'name' => \Str::random(10),
                    'sectionIndex' => "1",
                    'filterCategories' => [
                        [
                            "name" => \Str::random(10),
                            "filterTypes" => [
                                [
                                    "name" => \Str::random(10),
                                ]
                            ]
                        ]
                    ],
                    'filters' => [
                        [
                            'filterTypeIndex' => 0,
                            'filterCategoryIndex' => 0,
                            'filterConfigs' => [\Str::random(15)] 
                        ]
                    ]
                ],
            ]
        ];

        $apiURL = '/api/reports/'.$report->id;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertOk();
        $response->assertSuccessful();
        $response->assertJsonStructure();

        $this->assertDatabaseHas('cr_report', [
            'id' => $report->id,
            'name' => $requestData['name'],
            'description' => $requestData['description'],
        ]);

        $this->assertDatabaseHas('cr_section', [
            'sectionIndex' => $requestData['sections'][0]['sectionIndex'],
            'catalog' => $requestData['sections'][0]['name'],
        ]);

        $this->assertDatabaseHas('cr_filter_config', [
            'configValue' => $requestData['sections'][0]['filters'][0]['filterConfigs'],
        ]);
    }

    public function testUpdateFunctionException()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $report = Report::factory()->for($customer)->create([
            'name' => \Str::random(25)
        ]);

        $requestData = [
            'id' => $report->id,
        ];

        $apiURL = '/api/reports/'.$report->id;
        $response = $this->withHeaders($headers)->put($apiURL, $requestData);

        $response->assertStatus(422);
    }

    public function testDestroyFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $report = Report::factory()->for($customer)->create();

        $requestData = [
            'id' => $report->id,
        ];

        $apiURL = '/api/reports/'.$report->id;
        $response = $this->withHeaders($headers)->delete($apiURL);

        $response->assertOk();
        $response->assertSuccessful();
        $this->assertSoftDeleted('cr_report', ['id' => $report->id]);
    }

    public function testColorsAtTimeOfCompletionFunctionException()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $location = Location::factory()->for($customer)->create();
        $profileAssignedTo = Profile::factory()->for($customer)->create();
        $userAssignedTo = User::factory()->for($customer)->create();

        Task::factory()->times(rand(1, 5))->for($userAssignedTo)->for($profileAssignedTo)->for($customer)->for($location)->create([
            'userCompleted' => $user->userID,
            'completedAt' => date('Y-m-d H:i:s'),
            'isTaskSet' => 1,
        ]);

        $requestData = [
            'startDate' => date('Y-m-d'),
            'endDate' => date('Y-m-d'),
        ];

        $apiURL = '/api/reports/colors-at-completion?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertStatus(500);
    }

    public function testGetTaskItemsReportFunctionCaseSearch()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $search = 'abcdefghij';
        $tasksReportCustom1 = TasksReportCustom::factory()->times(rand(2, 10))->for($customer)->create([
            "customReportName" => $search.\Str::random(5),
        ]);

        $tasksReportCustom2 = TasksReportCustom::factory()->times(rand(2, 10))->for($customer)->create([
            "customReportName" => \Str::random(5),
        ]);

        $tasksReportCustom3 = TasksReportCustom::factory()->times(rand(2, 10))->for($customer)->create();

        $requestData = [
            'search' => $search,
        ];

        $apiURL = '/api/reports/get-task-items-report?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();

        $requestArray = $tasksReportCustom1->pluck('customReportID')->toArray();
        $responseArray = collect($response->json('data'))->pluck('customReportID')->toArray();

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testGetTaskItemsReportFunctionCaseOrderBy()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $tasksReportCustom = TasksReportCustom::factory()->times(rand(2, 15))->for($customer)->create([
            "customReportName" => \Str::random(5),
        ]);

        $requestData = [
            'orderBy' => 'desc',
            'orderByField' => 'customReportName',
        ];

        $apiURL = '/api/reports/get-task-items-report?'.http_build_query($requestData);
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();

        $requestArray = $tasksReportCustom->pluck('customReportName')->toArray();
        $responseArray = collect($response->json('data'))->pluck('customReportName')->toArray();

        rsort($requestArray);

        $this->assertEquals($requestArray, $responseArray);
    }

    public function testViewTaskItemsReportFunction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $headers = $this->authenticateUser($user);

        $tasksReportCustom = TasksReportCustom::factory()->for($customer)->create();
        $tasksReportCustomItem = TasksReportCustomItem::factory()->times(rand(1, 5))->for($tasksReportCustom)->create();
        $itemBatchID = collect($tasksReportCustomItem)->pluck('itemBatchID')->toArray();

        $location = Location::factory()->for($customer)->create();
        $task = Task::factory()->for($customer)->for($location)->create(['dueAt' => date('Y-m-d H:i:s')]);
        $taskItem = TaskItem::factory()->for($customer)->for($task)->create([
            'itemBatchID' => implode(',', $itemBatchID)
        ]);

        $apiURL = '/api/reports/task-items-report-view/'.$tasksReportCustom->customReportID;
        $response = $this->withHeaders($headers)->get($apiURL);

        $response->assertOk();
        $response->assertSuccessful();

        $requestArray = $tasksReportCustom->customReportName;
        $responseArray = $response->json('report.customReportName');

        $this->assertEquals($requestArray, $responseArray);
    }
}
