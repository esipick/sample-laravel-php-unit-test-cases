<?php

namespace Feature\Models\Others;

use App\Models\Filter;
use App\Models\FilterConfig;
use Tests\TestCase;

class FilterTest extends TestCase
{
    public function testCreateFilter()
    {
        $filter = Filter::factory()->create([
            'reportID' => 1,
            'sectionIndex' => 1,
            'filterCategory' => 'category_1',
            'filterType' => 'type_1',
        ]);
        FilterConfig::factory()->count(5)->create(['filterCategory' => 'category_1', 'reportID' => 1]);
        $this->assertInstanceOf(Filter::class, $filter);
    }

    public function testFilterConfigsRelationship()
    {
        $filter = Filter::factory()->create([
            'reportID' => 1,
            'sectionIndex' => 1,
            'filterCategory' => 'category_1',
            'filterType' => 'type_1',
        ]);
        FilterConfig::factory()->count(5)->create(['filterCategory' => 'category_1', 'reportID' => 1]);
        $this->assertInstanceOf(FilterConfig::class, $filter->filterConfigs->first());
    }
}
