<?php

namespace Subhashladumor1\LaravelAiVideo\Drivers;

use Subhashladumor1\LaravelAiVideo\Contracts\VideoDriver;
use Illuminate\Support\Facades\Http;
use Exception;

class OpenAIDriver implements VideoDriver
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function textToVideo(string $prompt, array $options = []): string
    {
        throw new Exception("OpenAI Driver does not support direct textToVideo generation yet. Use ComposedDriver.");
    }

    public function imageToVideo($imagePath, array $options = []): string
    {
        throw new Exception("OpenAI Driver does not support imageToVideo generation.");
    }

    public function generateScenes(string $script, array $options = []): array
    {
        $model = $options['model'] ?? 'gpt-4-turbo';

        $prompt = "Break this script into cinematic visual scenes with descriptions and duration. Return strictly valid JSON array of objects with keys: 'scene_number', 'visual_description', 'voiceover_text', 'duration_seconds'. Script: " . $script;

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional video director.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->failed()) {
            throw new Exception("OpenAI scene generation failed: " . $response->body());
        }

        $data = $response->json('choices.0.message.content');
        $scenes = json_decode($data, true);

        if (isset($scenes['scenes'])) {
            return $scenes['scenes'];
        }

        return $scenes ?? [];
    }

    public function generateVoice(string $text, string $voiceId = 'alloy', array $options = []): string
    {
        $model = $options['model'] ?? 'tts-1';
        $path = $options['output_path'] ?? tempnam(sys_get_temp_dir(), 'ai_voice_') . '.mp3';

        $response = Http::withToken($this->apiKey)
            ->sink($path)
            ->post("{$this->baseUrl}/audio/speech", [
                'model' => $model,
                'input' => $text,
                'voice' => $voiceId,
            ]);

        if ($response->failed()) {
            if (file_exists($path))
                unlink($path);
            throw new Exception("OpenAI voice generation failed: " . $response->body());
        }

        return $path;
    }

    /**
     * Generate image from text (DALL-E).
     * Not part of VideoDriver interface but used by ComposedDriver.
     */
    public function generateImage(string $prompt, string $outputPath, array $options = []): string
    {
        $model = $options['model'] ?? 'dall-e-3';
        $size = $options['size'] ?? '1024x1024';

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/images/generations", [
                'model' => $model,
                'prompt' => $prompt,
                'n' => 1,
                'size' => $size,
                'response_format' => 'b64_json', // prefer base64 to avoid extra download
            ]);

        if ($response->failed()) {
            throw new Exception("OpenAI image generation failed: " . $response->body());
        }

        $b64 = $response->json('data.0.b64_json');
        if ($b64) {
            file_put_contents($outputPath, base64_decode($b64));
            return $outputPath;
        }

        // Fallback for url
        $url = $response->json('data.0.url');
        if ($url) {
            file_put_contents($outputPath, file_get_contents($url));
            return $outputPath;
        }

        throw new Exception("OpenAI returned no image data.");
    }

    public function estimateCost(string $type, array $params = []): float
    {
        if ($type === 'scene-planning') {
            $inputTokens = strlen($params['script'] ?? '') / 4;
            return ($inputTokens / 1000) * 0.01 + 0.03;
        }
        if ($type === 'voice') {
            $chars = strlen($params['text'] ?? '');
            return ($chars / 1000) * 0.015;
        }
        if ($type === 'image') {
            return 0.04; // DALL-E 3 Standard
        }
        return 0.0;
    }
}
