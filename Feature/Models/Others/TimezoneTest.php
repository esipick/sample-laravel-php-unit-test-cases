<?php

namespace Feature\Models\Others;

use App\Models\Timezone;
use Tests\TestCase;

class TimezoneTest extends TestCase
{
    public function testCanCreateTimeZone()
    {
        $insertArray = [
            'name' => 'UTC-1',
            'gmtOffset' => '+1',
        ];

        $timezone = Timezone::create($insertArray);

        $this->assertInstanceOf(Timezone::class, $timezone);
        $this->assertEquals($insertArray['name'], $timezone->name);
        $this->assertEquals($insertArray['gmtOffset'], $timezone->gmtOffset);
    }

    public function testCanUpdateTimeZone()
    {
        $insertArray = [
            'name' => 'UTC-1',
            'gmtOffset' => '+1',
        ];

        $timezone = Timezone::create($insertArray);

        $updateArray = [
            'name' => 'UTC-2',
            'gmtOffset' => '+2',
        ];

        $timezone->update($updateArray);

        $this->assertInstanceOf(Timezone::class, $timezone);
        $this->assertEquals($updateArray['name'], $timezone->name);
        $this->assertEquals($updateArray['gmtOffset'], $timezone->gmtOffset);
    }
}
