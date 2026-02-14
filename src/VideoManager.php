<?php

namespace Subhashladumor1\LaravelAiVideo;

use Illuminate\Support\Manager;
use Subhashladumor1\LaravelAiVideo\Drivers\OpenAIDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\StabilityDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\RunwayDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\ComposedDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\GuardAwareVideoDriver;
use Subhashladumor1\LaravelAiVideo\Contracts\VideoDriver;

class VideoManager extends Manager
{
    public function getDefaultDriver()
    {
        return $this->container->make('config')->get('ai-video.default_driver', 'openai');
    }

    /**
     * Override driver method to wrap in Guard if enabled.
     */
    public function driver($driver = null)
    {
        $driverName = $driver ?: $this->getDefaultDriver();
        $instance = parent::driver($driverName);

        // Wrap in Guard Proxy if enabled
        if ($this->container->make('config')->get('ai-video.guard.enabled', true)) {
            // Avoid double wrapping if cached by Manager.
            // Manager caches by name. If we modify the instance here, implementation details matter.
            // Laravel Manager returns the resolved instance.
            // But we want to return a decorated instance.

            // Note: Manager stores the instance in $this->drivers[$driverName].
            // We should ideally wrap it in the creator methods, BUT we might want to toggle guard dynamically.

            // Simplest way: Check if it's already wrapped or wrap inside the create methods.
            // Use a specific method to wrap.
            if (!($instance instanceof GuardAwareVideoDriver)) {
                $instance = new GuardAwareVideoDriver($instance, $driverName);
                // Update cache if we want persistent wrapping
                // $this->drivers[$driverName] = $instance; 
            }
        }

        return $instance;
    }

    protected function createOpenAIDriver(): VideoDriver
    {
        // SDK Integration Logic
        $config = $this->container->make('config')->get('ai-video.drivers.openai');

        // If API Key is missing, try to get from SDK config
        if (empty($config['api_key']) && $this->shouldUseSdk()) {
            $config['api_key'] = config('ai-sdk.drivers.openai.api_key');
        }

        return new OpenAIDriver($config['api_key']);
    }

    protected function createStabilityDriver(): VideoDriver
    {
        $config = $this->container->make('config')->get('ai-video.drivers.stability');
        return new StabilityDriver($config['api_key']);
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
