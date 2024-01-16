<?php

namespace Feature\Models\Others;

use App\Models\Catalog;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    public function test_catalog_has_correct_fillable_properties()
    {
        $catalog = new Catalog;
        $fillable = $catalog->getFillable();

        $this->assertEquals(['name', 'description'], $fillable);
    }

    public function test_catalog_has_correct_table_name()
    {
        $catalog = new Catalog;
        $table = $catalog->getTable();

        $this->assertEquals('cr_catalog', $table);
    }

    public function test_catalog_has_many_catalog_filter_categories()
    {
        $catalog = new Catalog;
        $relationship = $catalog->catalogFilterCategory();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertEquals('catalog', $relationship->getForeignKeyName());
        $this->assertEquals('name', $relationship->getLocalKeyName());
    }
}
