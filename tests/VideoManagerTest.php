<?php

namespace Subhashladumor1\LaravelAiVideo\Tests;

use Subhashladumor1\LaravelAiVideo\VideoManager;
use Subhashladumor1\LaravelAiVideo\Drivers\OpenAIDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\StabilityDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\RunwayDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\ComposedDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\GuardAwareVideoDriver;
use Subhashladumor1\LaravelAiVideo\Support\AiGuardIntegration;

class VideoManagerTest extends TestCase
{
    /** @test */
    public function it_can_instantiate_the_manager()
    {
        $manager = $this->app->make('ai-video');
        $this->assertInstanceOf(VideoManager::class, $manager);
    }

    /** @test */
    public function it_can_resolve_default_driver_without_guard()
    {
        // Disable guard for explicit type checking
        $this->app['config']->set('ai-video.guard.enabled', false);

        $manager = $this->app->make('ai-video');
        $driver = $manager->driver();
        $this->assertInstanceOf(OpenAIDriver::class, $driver);
    }

    /** @test */
    public function it_can_resolve_stability_driver_without_guard()
    {
        $this->app['config']->set('ai-video.guard.enabled', false);
        $this->app['config']->set('ai-video.drivers.stability.api_key', 'test-key');

        $manager = $this->app->make('ai-video');
        $driver = $manager->driver('stability');
        $this->assertInstanceOf(StabilityDriver::class, $driver);
    }

    /** @test */
    public function it_can_resolve_composed_driver_without_guard()
    {
        $this->app['config']->set('ai-video.guard.enabled', false);
        $this->app['config']->set('ai-video.drivers.openai.api_key', 'test-key');
        $this->app['config']->set('ai-video.drivers.stability.api_key', 'test-key');

        $manager = $this->app->make('ai-video');
        $driver = $manager->driver('composed');
        $this->assertInstanceOf(ComposedDriver::class, $driver);
    }

    /** @test */
    public function it_wraps_driver_in_guard_when_enabled()
    {
        // Guard enabled by default in config (set in TestCase) or ai-video.php
        $this->app['config']->set('ai-video.guard.enabled', true);

        $manager = $this->app->make('ai-video');
        $driver = $manager->driver('openai');

        $this->assertInstanceOf(GuardAwareVideoDriver::class, $driver);
    }

    /** @test */
    public function it_checks_guard_availability()
    {
        $this->assertFalse(AiGuardIntegration::isAvailable());
    }
}
