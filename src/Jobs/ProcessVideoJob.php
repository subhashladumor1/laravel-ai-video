<?php

namespace Subhashladumor1\LaravelAiVideo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Subhashladumor1\LaravelAiVideo\VideoManager;
use Subhashladumor1\LaravelAiVideo\Support\AiGuardIntegration;
use Subhashladumor1\LaravelAiVideo\Drivers\GuardAwareVideoDriver;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200; // 20 minutes for long renders
    public $tries = 3;

    protected string $type;
    protected mixed $input;
    protected array $options;
    protected string $driverName;

    public function __construct(string $type, $input, array $options = [], string $driverName = 'composed')
    {
        $this->type = $type;
        $this->input = $input;
        $this->options = $options;
        $this->driverName = $driverName;
    }

    public function handle(VideoManager $manager)
    {
        Log::info("Starting AI video generation job.", [
            'type' => $this->type,
            'driver' => $this->driverName
        ]);

        try {
            $driver = $manager->driver($this->driverName);

            // Check if request is already guarded by the driver wrapper
            $isGuarded = ($driver instanceof GuardAwareVideoDriver);

            // If not guarded by wrapper, apply explicit guard checks
            $estimatedCost = 0.0;
            if (!$isGuarded) {
                $estimatedCost = $driver->estimateCost($this->type, ['input' => $this->input, 'options' => $this->options]);

                // 1. AI Guard Check (Pre-flight)
                AiGuardIntegration::checkBudget($estimatedCost, $this->driverName);
            }

            // 2. Execution
            $resultPath = '';

            if ($this->type === 'text-to-video') {
                $resultPath = $driver->textToVideo($this->input, $this->options);
            } elseif ($this->type === 'image-to-video') {
                $resultPath = $driver->imageToVideo($this->input, $this->options);
            } else {
                throw new Exception("Unknown video processing type: {$this->type}");
            }

            Log::info("Video generated successfully.", ['path' => $resultPath]);

            // 3. Log Usage (Post-flight)
            if (!$isGuarded) {
                AiGuardIntegration::logUsage($estimatedCost, $this->driverName, [
                    'type' => $this->type,
                    'path' => $resultPath
                ]);
            }

        } catch (Exception $e) {
            Log::error("Video processing failed: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
