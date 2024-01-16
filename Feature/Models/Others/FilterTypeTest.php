<?php

namespace Feature\Models\Others;

use App\Models\FilterCategory;
use App\Models\FilterType;
use Tests\TestCase;

class FilterTypeTest extends TestCase
{
    public function testFilterTypeBelongsToFilterCategory()
    {
        $filterCategory = FilterCategory::factory()->create(['name' => 'test']);
        $filterType = FilterType::factory()->create(['filterCategory' => $filterCategory->name]);

        $relatedFilterCategory = $filterType->filterCategoryInfo;

        $this->assertInstanceOf(FilterCategory::class, $relatedFilterCategory);
        $this->assertEquals($filterCategory->name, $relatedFilterCategory->name);
    }
}
