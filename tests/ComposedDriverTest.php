<?php

namespace Subhashladumor1\LaravelAiVideo\Tests;

use Subhashladumor1\LaravelAiVideo\Drivers\ComposedDriver;
use Subhashladumor1\LaravelAiVideo\Contracts\VideoDriver;
use Mockery;

class ComposedDriverTest extends TestCase
{
    /** @test */
    public function it_calculates_scenes_correctly()
    {
        if (!class_exists('Mockery')) {
            $this->markTestSkipped('Mockery not installed');
        }

        // We can't easily mock internal creation of drivers unless refactored.
        // But we can check estimateCost logic which is pure.

        $driver = new ComposedDriver([]);

        // 15 seconds / 5 secs per scene = 3 scenes.
        // Cost = 0.03 + 3 * (0.04 + 0.20 + 0.015) = 0.03 + 3 * 0.255 = 0.03 + 0.765 = 0.795
        $cost = $driver->estimateCost('text-to-video', ['options' => ['duration' => 15]]);

        $this->assertEquals(0.795, $cost);
    }
}
