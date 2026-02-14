<?php

namespace Subhashladumor1\LaravelAiVideo\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Subhashladumor1\LaravelAiVideo\LaravelAiVideoServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelAiVideoServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Mock config
        $app['config']->set('ai-video.default_driver', 'openai');
        $app['config']->set('ai-video.drivers.openai.api_key', 'test-key');
        // $app['config']->set('ai-video.drivers.stability.api_key', 'test-key');
    }
}
