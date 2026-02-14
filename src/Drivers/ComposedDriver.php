<?php

namespace Subhashladumor1\LaravelAiVideo\Drivers;

use Subhashladumor1\LaravelAiVideo\Contracts\VideoDriver;
use Subhashladumor1\LaravelAiVideo\ScenePlanner;
use Subhashladumor1\LaravelAiVideo\VoiceGenerator;
use Subhashladumor1\LaravelAiVideo\SubtitleGenerator;
use Subhashladumor1\LaravelAiVideo\VideoRenderer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;

class ComposedDriver implements VideoDriver
{
    protected array $config;
    protected OpenAIDriver $openaiDriver;
    protected StabilityDriver $stabilityDriver;

    public function __construct(array $config = [])
    {
        $this->config = $config;

        $openaiKey = $this->getApiKey('openai');
        $stabilityKey = $this->getApiKey('stability');

        $this->openaiDriver = new OpenAIDriver($openaiKey);
        $this->stabilityDriver = new StabilityDriver($stabilityKey);
    }

    protected function getApiKey(string $driver): string
    {
        // 1. Try Local Config
        $key = Config::get("ai-video.drivers.{$driver}.api_key");

        // 2. Try SDK Config
        if (empty($key) && Config::get('ai-video.use_sdk', false)) {
            $key = Config::get("ai-sdk.drivers.{$driver}.api_key");
        }

        return $key ?? '';
    }

    public function textToVideo(string $prompt, array $options = []): string
    {
        Log::info("ComposedDriver: Starting Text-to-Video generation.", ['prompt' => $prompt]);

        // 1. Plan Scenes (Using OpenAI)
        $planner = new ScenePlanner($this->openaiDriver);
        $scenes = $planner->plan($prompt);

        $processedScenes = [];
        $tempDir = Config::get('ai-video.temp_path', sys_get_temp_dir() . '/ai-video') . '/' . uniqid();
        if (!is_dir($tempDir))
            mkdir($tempDir, 0777, true);

        foreach ($scenes as $index => $scene) {
            $imagePath = $tempDir . "/scene_{$index}.png";
            $videoPath = $tempDir . "/scene_{$index}.mp4";

            // 2. Visual Generation (DALL-E 3)
            // Use visual description from the planned scene
            try {
                $this->openaiDriver->generateImage($scene['visual_description'], $imagePath);
            } catch (Exception $e) {
                Log::error("Image generation failed for scene {$index}: " . $e->getMessage());
                throw $e;
            }

            // 3. Animate (Stability SVD)
            try {
                $duration = $scene['duration_seconds'] ?? 4;
                // Stability SVD usually fixed duration/frames, but we pass options just in case
                // For MVP, SVD is short clips. We might loop or extend if duration is long.
                // But let's assume 1:1 for now.

                // Note: stabilityDriver logic handles async, but here we need it synchronous?
                // The implemented driver's imageToVideo waits/polls. So it is effectively synchronous.
                $generatedVideo = $this->stabilityDriver->imageToVideo($imagePath, ['duration' => $duration]);

                // Move or copy to our temp structure if needed, or use returned path
                copy($generatedVideo, $videoPath);

            } catch (Exception $e) {
                Log::error("Video animation failed for scene {$index}: " . $e->getMessage());
                throw $e;
            }

            // 4. Voiceover
            $voicePath = $this->openaiDriver->generateVoice($scene['voiceover_text'], 'alloy', ['output_path' => $tempDir . "/voice_{$index}.mp3"]);

            $processedScenes[] = [
                'video_path' => $videoPath,
                'audio_path' => $voicePath,
                'duration' => $duration,
            ];
        }

        // 5. Render Final Video
        $renderer = new VideoRenderer(Config::get('ai-video.ffmpeg', []));
        $outputPath = $tempDir . '/final_merged_output.mp4';

        $finalPath = $renderer->render($processedScenes, $outputPath, $options);

        Log::info("ComposedDriver: Generation complete.", ['path' => $finalPath]);
        return $finalPath;
    }

    public function imageToVideo($imagePath, array $options = []): string
    {
        return $this->stabilityDriver->imageToVideo($imagePath, $options);
    }

    public function generateScenes(string $script, array $options = []): array
    {
        return $this->openaiDriver->generateScenes($script, $options);
    }

    public function generateVoice(string $text, string $voiceId, array $options = []): string
    {
        return $this->openaiDriver->generateVoice($text, $voiceId, $options);
    }

    public function estimateCost(string $type, array $params = []): float
    {
        // Dynamic Cost Estimation
        if ($type === 'text-to-video') {
            // 1 Base Scene Planning cost ($0.03) + N * (Image($0.04) + Motion($0.20) + Voice($0.015))
            // Stability video is expensive (~$0.20 or 20 credits)
            $duration = $params['options']['duration'] ?? 15;
            $sceneCount = ceil($duration / 5);
            return 0.03 + ($sceneCount * (0.04 + 0.20 + 0.015));
        }
        return 0.10;
    }
}
