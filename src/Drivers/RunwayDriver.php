<?php

namespace Subhashladumor1\LaravelAiVideo\Drivers;

use Subhashladumor1\LaravelAiVideo\Contracts\VideoDriver;
use Illuminate\Support\Facades\Http;
use Exception;

class RunwayDriver implements VideoDriver
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.runwayml.com/v1';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function textToVideo(string $prompt, array $options = []): string
    {
        // Typically Gen-2 endpoint
        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/tasks", [
                'type' => 'gen2',
                'prompts' => [['text' => $prompt]],
                'seconds' => $options['duration'] ?? 4,
            ]);

        if ($response->failed()) {
            throw new Exception("Runway text-to-video generation failed: " . $response->body());
        }

        $taskId = $response->json('id');
        return $this->pollTask($taskId);
    }

    public function imageToVideo($imagePath, array $options = []): string
    {
        // Assuming image upload first or direct base64/url
        // Simplification: assume URL if string starts with http, else upload?
        // Runway API usually takes URLs.
        // For local file, might need upload first mechanism or just assume accessible URL for MVP.
        $imageUrl = $imagePath;

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/tasks", [
                'type' => 'gen2',
                'prompts' => [['image' => $imageUrl]], // Simplified
                'seconds' => $options['duration'] ?? 4,
            ]);

        if ($response->failed()) {
            throw new Exception("Runway image-to-video generation failed: " . $response->body());
        }

        $taskId = $response->json('id');
        return $this->pollTask($taskId);
    }

    protected function pollTask(string $taskId): string
    {
        $maxRetries = 120; // 2-4 minutes

        for ($i = 0; $i < $maxRetries; $i++) {
            sleep(2);
            $check = Http::withToken($this->apiKey)
                ->get("{$this->baseUrl}/tasks/{$taskId}");

            $status = $check->json('status');

            if ($status === 'SUCCEEDED') {
                // Return result
                $url = $check->json('output.0'); // Example structure
                // Download to temp
                $path = tempnam(sys_get_temp_dir(), 'runway_') . '.mp4';
                file_put_contents($path, file_get_contents($url));
                return $path;
            }
            if ($status === 'FAILED') {
                throw new Exception("Runway generation failed: " . $check->body());
            }
        }

        throw new Exception("Runway generation timed out.");
    }

    public function generateScenes(string $script, array $options = []): array
    {
        throw new Exception("RunwayDriver does not support scene generation.");
    }

    public function generateVoice(string $text, string $voiceId, array $options = []): string
    {
        throw new Exception("RunwayDriver does not support voice generation.");
    }

    public function estimateCost(string $type, array $params = []): float
    {
        // Gen2 is roughly $0.05 per second.
        if ($type === 'text-to-video' || $type === 'image-to-video') {
            $seconds = $params['duration'] ?? 4;
            return $seconds * 0.05;
        }
        return 0.0;
    }
}
