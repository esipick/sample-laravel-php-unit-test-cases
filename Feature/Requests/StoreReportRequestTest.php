<?php

namespace Tests\Feature\Requests;

use App\Http\Requests\StoreReportRequest;
use App\Models\Customer;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreReportRequestTest extends TestCase
{
    public function testAuthorizeReturnsTrueWhenUserIsAuthenticated()
    {
        Auth::shouldReceive('check')->andReturn(true);

        $request = new StoreReportRequest();

        $this->assertTrue($request->authorize());
    }
    public function testValidDataPassesValidation()
    {
        $customer = Customer::factory()->create();
        $search = 'abcdefghij';
        $report1 = Report::factory()->for($customer)->create([
            'name' => $search.\Str::random(5),
        ]);

        $validData = [
            'id' => $report1->id,
            'name' => 'Valid Report Name',
            'description' => 'Valid Report Description',
            'sections' => [
                [
                    'name' => 'Section 1',
                    'description' => 'Section Description',
                    'sectionIndex' => 0,
                    'filterCategories' => [
                        [
                            'name' => 'Category 1',
                            'description' => 'Category Description',
                            'discriminator' => 1,
                            'filterTypes' => [
                                [
                                    'name' => 'Filter Type 1',
                                    'description' => 'Filter Type Description',
                                ],
                            ],
                        ],
                    ],
                    'filters' => [
                        [
                            'filterCategoryIndex' => 0,
                            'filterConfigs' => [],
                            'filterTypeIndex' => 0,
                        ],
                    ],
                ],
            ],
        ];

        $validator = Validator::make($validData, (new StoreReportRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    public function testRequiredFieldsAreChecked()
    {
        $invalidData = [
            // Missing required 'name' field
            'description' => 'Invalid Report Description',
            'sections' => [],
        ];

        $validator = Validator::make($invalidData, (new StoreReportRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function testInvalidDataFailsValidation()
    {
        $invalidData = [
            'id' => 'invalid', // 'id' should be an integer
            'name' => 'Valid Report Name',
            'description' => 'Valid Report Description',
            'sections' => [
                [
                    'name' => 'Section 1',
                    'description' => 'Section Description',
                    'sectionIndex' => 'invalid', // 'sectionIndex' should be an integer
                    'filterCategories' => [
                        [
                            'name' => 'Category 1',
                            'description' => 'Category Description',
                            'discriminator' => 'invalid', // 'discriminator' should be an integer
                            'filterTypes' => [
                                [
                                    'name' => 'Filter Type 1',
                                    'description' => 'Filter Type Description',
                                ],
                            ],
                        ],
                    ],
                    'filters' => [
                        [
                            'filterCategoryIndex' => 'invalid', // 'filterCategoryIndex' should be an integer
                            'filterConfigs' => [],
                            'filterTypeIndex' => 'invalid', // 'filterTypeIndex' should be an integer
                        ],
                    ],
                ],
            ],
        ];

        $validator = Validator::make($invalidData, (new StoreReportRequest())->rules());

        $this->assertTrue($validator->fails());
        // Add assertions for other fields as needed
    }

    public function testValidDataWithFilterTypesPassesValidation()
    {
        $validData = [
            'name' => 'Valid Report Name',
            'description' => 'Valid Report Description',
            'sections' => [
                [
                    'name' => 'Section 1',
                    'description' => 'Section Description',
                    'sectionIndex' => 0,
                    'filterCategories' => [
                        [
                            'name' => 'Category 1',
                            'description' => 'Category Description',
                            'discriminator' => 1,
                            'filterTypes' => [
                                [
                                    'name' => 'Filter Type 1',
                                    'description' => 'Filter Type Description',
                                ],
                            ],
                        ],
                    ],
                    'filters' => [
                        [
                            'filterCategoryIndex' => 0,
                            'filterConfigs' => [],
                            'filterTypeIndex' => 0,
                        ],
                    ],
                ],
            ],
        ];

        $validator = Validator::make($validData, (new StoreReportRequest())->rules());

        $this->assertFalse($validator->fails());
    }
}
