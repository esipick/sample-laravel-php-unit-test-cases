<?php

namespace Feature\Models\Others;

use App\Models\Catalog;
use App\Models\Customer;
use App\Models\Filter;
use App\Models\FilterConfig;
use App\Models\Report;
use App\Models\Section;
use Tests\TestCase;

class ReportTest extends TestCase
{
    public function testBelongsToRelationship()
    {
        $customer = Customer::factory()->create();
        $report = Report::factory()->for($customer)->create();

        $this->assertInstanceOf(Report::class, $report);
        $this->assertInstanceOf(Customer::class, $report->customer);
    }

    public function testHasManyRelationship()
    {
        $customer = Customer::factory()->create();
        $report = Report::factory()->for($customer)->create();

        $catalog = Catalog::factory()->create();
        Section::factory()->create([
            'catalog' => $catalog->id,
            'reportID' => $report->id,
        ]);
        $this->assertInstanceOf(Section::class, $report->sections[0]);

        Filter::create([
            'reportID' => $report->id,
            'sectionIndex' => '1',
            'filterCategory' => 'Section Heading',
            'filterType' => 'Last 30 Days',
        ]);
        $this->assertInstanceOf(Filter::class, $report->filters[0]);

        FilterConfig::factory()->create([
            'reportID' => $report->id,
            'sectionIndex' => '0',
            'filterCategory' => 'Section Heading',
            'configValue' => 'some config value',
        ]);
        $this->assertInstanceOf(FilterConfig::class, $report->filterConfigs[0]);
    }
}
