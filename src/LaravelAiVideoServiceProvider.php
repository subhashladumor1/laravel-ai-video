<?php

namespace Subhashladumor1\LaravelAiVideo;

use Illuminate\Support\ServiceProvider;

class LaravelAiVideoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/ai-video.php', 'ai-video');

        $this->app->singleton('ai-video', function ($app) {
            return new VideoManager($app);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/ai-video.php' => config_path('ai-video.php'),
            ], 'ai-video-config');

            // Register commands if needed
            // $this->commands([]);
        }
    }
}
