<?php

namespace Subhashladumor1\LaravelAiVideo;

use Subhashladumor1\LaravelAiVideo\Contracts\VideoDriver;
use Subhashladumor1\LaravelAiVideo\Drivers\OpenAIDriver;
use Exception;

class ScenePlanner
{
    protected VideoDriver $driver;

    public function __construct(VideoDriver $driver)
    {
        // Typically uses OpenAI driver for intelligence
        $this->driver = $driver;
    }

    public function plan(string $script): array
    {
        if (!method_exists($this->driver, 'generateScenes')) {
            throw new Exception("Driver does not support scene generation.");
        }

        return $this->driver->generateScenes($script);
    }
}
