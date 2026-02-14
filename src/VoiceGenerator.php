<?php

namespace Subhashladumor1\LaravelAiVideo;

use Subhashladumor1\LaravelAiVideo\Contracts\VideoDriver;
use Exception;

class VoiceGenerator
{
    protected VideoDriver $driver;

    public function __construct(VideoDriver $driver)
    {
        $this->driver = $driver;
    }

    public function generate(string $text, string $voiceId = 'alloy'): string
    {
        if (!method_exists($this->driver, 'generateVoice')) {
            throw new Exception("Driver does not support voice generation.");
        }

        return $this->driver->generateVoice($text, $voiceId);
    }
}
