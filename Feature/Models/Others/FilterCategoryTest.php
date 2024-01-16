<?php

namespace Feature\Models\Others;

use App\Models\FilterCategory;
use App\Models\FilterType;
use Tests\TestCase;

class FilterCategoryTest extends TestCase
{
    public function testCreateFilterCategory()
    {
        $filterCategory = FilterCategory::factory()->create();

        $this->assertInstanceOf(FilterCategory::class, $filterCategory);
        $this->assertDatabaseHas('cr_filter_category', ['name' => $filterCategory->name]);
    }

    public function testFilterTypeRelationship()
    {
        $filterCategory = FilterCategory::factory()->create(['name' => 'test']);
        FilterType::factory()->count(5)->create(['filterCategory' => $filterCategory->name]);
        $this->assertInstanceOf(FilterType::class, $filterCategory->filterType->first());
    }
}
