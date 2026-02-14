<?php

namespace Subhashladumor1\LaravelAiVideo;

use Illuminate\Support\Manager;
use Subhashladumor1\LaravelAiVideo\Drivers\OpenAIDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\LeonardoDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\GeminiDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\RunwayDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\ComposedDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\GuardAwareVideoDriver;
use Subhashladumor1\LaravelAiVideo\Contracts\VideoDriver;

class VideoManager extends Manager
{
    // ... (getDefaultDriver and driver methods remain same, effectively handling wrapping) ...

    public function getDefaultDriver()
    {
        return $this->container->make('config')->get('ai-video.default_driver', 'openai');
    }

    public function driver($driver = null)
    {
        $driverName = $driver ?: $this->getDefaultDriver();
        $instance = parent::driver($driverName);

        // Wrap in Guard Proxy if enabled
        if ($this->container->make('config')->get('ai-video.guard.enabled', true)) {
            if (!($instance instanceof GuardAwareVideoDriver)) {
                $instance = new GuardAwareVideoDriver($instance, $driverName);
            }
        }

        return $instance;
    }

    protected function createOpenAIDriver(): VideoDriver
    {
        $config = $this->container->make('config')->get('ai-video.drivers.openai');
        if (empty($config['api_key']) && $this->shouldUseSdk()) {
            $config['api_key'] = config('ai-sdk.drivers.openai.api_key');
        }
        return new OpenAIDriver($config['api_key']);
    }

    protected function createLeonardoDriver(): VideoDriver
    {
        $config = $this->container->make('config')->get('ai-video.drivers.leonardo');
        return new LeonardoDriver($config['api_key']);
    }

    protected function createGeminiDriver(): VideoDriver
    {
        $config = $this->container->make('config')->get('ai-video.drivers.gemini');
        return new GeminiDriver($config['api_key']);
    }

    protected function createRunwayDriver(): VideoDriver
    {
        $config = $this->container->make('config')->get('ai-video.drivers.runway');
        return new RunwayDriver($config['api_key']);
    }

    protected function createComposedDriver(): VideoDriver
    {
        $config = $this->container->make('config')->get('ai-video.drivers.composed', []);

        // For composed driver, the sub-drivers (OpenAI, Stability) are created internally.
        // We should ensure THOSE are also guarded or configured correctly.
        // But ComposedDriver creates new instances of OpenAIDriver manually.
        // Refactor ComposedDriver to use the Manager or Injection to respect Guard settings?
        // Ideally ComposedDriver should accept driver instances. 
        // For simplicity, we wrap the ComposedDriver itself, so the Aggregate Cost is checked.

        return new ComposedDriver($config);
    }

    protected function shouldUseSdk(): bool
    {
        return $this->container->make('config')->get('ai-video.use_sdk', false)
            && class_exists(\Subhashladumor1\LaravelAiSdk\Facades\AiSdk::class);
    }
}
