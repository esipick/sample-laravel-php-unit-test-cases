<?php

namespace Feature\Models\Others;

use App\Models\FilterConfig;
use Tests\TestCase;

class FilterConfigTest extends TestCase
{
    public function testCreateFilterConfig()
    {
        $filterConfig = FilterConfig::factory()->create();

        $this->assertInstanceOf(FilterConfig::class, $filterConfig);
        $this->assertDatabaseHas('cr_filter_config', ['configID' => $filterConfig->configID]);
    }

    public function testSoftDeleteFilterConfig()
    {
        $filterConfig = FilterConfig::factory()->create();

        $this->assertNull($filterConfig->deleted_at);

        $filterConfig->delete();

        $this->assertNotNull($filterConfig->deleted_at);
        $this->assertSoftDeleted($filterConfig);
    }
}
