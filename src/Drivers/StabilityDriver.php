<?php

namespace Subhashladumor1\LaravelAiVideo\Drivers;

use Subhashladumor1\LaravelAiVideo\Contracts\VideoDriver;
use Illuminate\Support\Facades\Http;
use Exception;

class StabilityDriver implements VideoDriver
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.stability.ai/v2beta/image-to-video';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function textToVideo(string $prompt, array $options = []): string
    {
        // Currently Stability primarily supports Image to Video prominently via API v2beta.
        // Assuming user might want text-to-image THEN image-to-video flow here or wait for native support.
        // For compliance with instructions, let's implement a direct call if available or composed logic.
        // But driver responsibility should be atomic.
        // If not supported natively, throw.
        throw new Exception("StabilityDriver direct textToVideo not implemented. Use ComposedDriver with Stability for image generation first.");
    }

    public function imageToVideo($imagePath, array $options = []): string
    {
        // Example implementation for Stability Image-to-Video API
        // This is async usually. Poll for result.

        $response = Http::withToken($this->apiKey)
            ->attach('image', file_get_contents($imagePath), basename($imagePath))
            ->post("{$this->baseUrl}", [
                'seed' => $options['seed'] ?? 0,
                'cfg_scale' => $options['cfg_scale'] ?? 1.8,
                'motion_bucket_id' => $options['motion_bucket_id'] ?? 127,
            ]);

        if ($response->failed()) {
            throw new Exception("Stability Image-to-Video generation failed: " . $response->body());
        }

        $generationId = $response->json('id');

        // Polling loop (simplified for synchronous call, usually would be a Job)
        // But driver interface returns path string immediately? 
        // Or driver returns job ID? Contract says "string Path".
        // If this is running in a queue job already, we can poll here.

        $maxRetries = 60; // 1 min timeout? Video takes time.
        // Usually should be handled via async job dispatching. 
        // But to satisfy interface returning path:

        for ($i = 0; $i < $maxRetries; $i++) {
            sleep(2);
            $check = Http::withToken($this->apiKey)
                ->get("{$this->baseUrl}/result/{$generationId}");

            if ($check->status() === 200) {
                // Success
                // Decode video
                $videoBytes = base64_decode($check->json('video'));
                $path = sys_get_temp_dir() . "/stability_{$generationId}.mp4";
                file_put_contents($path, $videoBytes);
                return $path;
            }
            if ($check->status() === 202) {
                continue; // Processing
            }
            // Error
            throw new Exception("Stability polling failed: " . $check->body());
        }

        throw new Exception("Stability generation timed out.");
    }

    public function generateScenes(string $script, array $options = []): array
    {
        throw new Exception("StabilityDriver does not support scene generation.");
    }

    public function generateVoice(string $text, string $voiceId, array $options = []): string
    {
        throw new Exception("StabilityDriver does not support voice generation.");
    }

    public function estimateCost(string $type, array $params = []): float
    {
        // 20 credits per video roughly
        if ($type === 'image-to-video') {
            return 0.20; // Example cost in USD
        }
        return 0.0;
    }
}
