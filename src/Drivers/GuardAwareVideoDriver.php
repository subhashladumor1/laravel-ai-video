<?php

namespace Subhashladumor1\LaravelAiVideo\Drivers;

use Subhashladumor1\LaravelAiVideo\Contracts\VideoDriver;
use Subhashladumor1\LaravelAiVideo\Support\AiGuardIntegration;
use Illuminate\Support\Facades\Log;

class GuardAwareVideoDriver implements VideoDriver
{
    protected VideoDriver $driver;
    protected string $driverName;

    public function __construct(VideoDriver $driver, string $driverName)
    {
        $this->driver = $driver;
        $this->driverName = $driverName;
    }

    public function textToVideo(string $prompt, array $options = []): string
    {
        $params = ['prompt' => $prompt, 'options' => $options];
        $estimatedCost = $this->driver->estimateCost('text-to-video', $params);

        AiGuardIntegration::checkBudget($estimatedCost, $this->driverName);

        $result = $this->driver->textToVideo($prompt, $options);

        AiGuardIntegration::logUsage($estimatedCost, $this->driverName, [
            'type' => 'text-to-video',
            'path' => $result
        ]);

        return $result;
    }

    public function imageToVideo($imagePath, array $options = []): string
    {
        $params = ['image' => $imagePath, 'options' => $options];
        $estimatedCost = $this->driver->estimateCost('image-to-video', $params);

        AiGuardIntegration::checkBudget($estimatedCost, $this->driverName);

        $result = $this->driver->imageToVideo($imagePath, $options);

        AiGuardIntegration::logUsage($estimatedCost, $this->driverName, [
            'type' => 'image-to-video',
            'path' => $result
        ]);

        return $result;
    }

    public function generateScenes(string $script, array $options = []): array
    {
        $params = ['script' => $script, 'options' => $options];
        $estimatedCost = $this->driver->estimateCost('scene-planning', $params);

        AiGuardIntegration::checkBudget($estimatedCost, $this->driverName);

        $result = $this->driver->generateScenes($script, $options);

        AiGuardIntegration::logUsage($estimatedCost, $this->driverName, [
            'type' => 'scene-planning',
            'scene_count' => count($result)
        ]);

        return $result;
    }

    public function generateVoice(string $text, string $voiceId, array $options = []): string
    {
        $params = ['text' => $text, 'voiceId' => $voiceId, 'options' => $options];
        $estimatedCost = $this->driver->estimateCost('voice', $params);

        AiGuardIntegration::checkBudget($estimatedCost, $this->driverName);

        $result = $this->driver->generateVoice($text, $voiceId, $options);

        AiGuardIntegration::logUsage($estimatedCost, $this->driverName, [
            'type' => 'voice',
            'path' => $result
        ]);

        return $result;
    }

    public function estimateCost(string $type, array $params = []): float
    {
        return $this->driver->estimateCost($type, $params);
    }
}
