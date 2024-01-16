<?php

namespace Feature\Models\Others;

use App\Models\Catalog;
use App\Models\Customer;
use App\Models\Filter;
use App\Models\Report;
use App\Models\Section;
use App\Models\Tasks\TasksReportCustom;
use Tests\TestCase;

class SectionTest extends TestCase
{
    public function testHasManyRelationship()
    {
        $customer = Customer::factory()->create();
        $report = Report::factory()->for($customer)->create();

        $catalog = Catalog::factory()->create();
        $section = Section::factory()->create([
            'catalog' => $catalog->id,
            'reportID' => $report->id,
        ]);

        Filter::create([
            'reportID' => $report->id,
            'sectionIndex' => '1',
            'filterCategory' => 'Section Heading',
            'filterType' => 'Last 30 Days',
        ]);
        $this->assertInstanceOf(Filter::class, $section->filters[0]);
    }

    public function testHasOneRelationship()
    {
        $customer = Customer::factory()->create();
        $report = TasksReportCustom::factory()->for($customer)->create();
        $filter = Filter::factory()->create(['reportID' => $report->customReportID]);
        $catalog = Catalog::factory()->create();
        $section = Section::factory()->create(['reportID' => $filter->reportID, 'catalog' => $catalog->name]);

        $this->assertInstanceOf(Catalog::class, $section->catalogRelation);
    }
}
