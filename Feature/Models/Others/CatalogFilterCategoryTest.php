<?php

namespace Feature\Models\Others;

use App\Models\CatalogFilterCategory;
use App\Models\FilterCategory;
use Tests\TestCase;

class CatalogFilterCategoryTest extends TestCase
{
    public function testFilterCategoryRelation()
    {
        $catalogFilterCategory = CatalogFilterCategory::factory()->create();

        $filterCategory = FilterCategory::factory()->create(['name' => $catalogFilterCategory->filterCategory]);
        $retrievedFilterCategory = $catalogFilterCategory->filterCategoryRelation;

        $this->assertEquals($retrievedFilterCategory->name, $filterCategory->name);
    }
}
