<?php

namespace Subhashladumor1\LaravelAiVideo\Contracts;

interface VideoDriver
{
    /**
     * Generate video from text prompt.
     *
     * @param string $prompt
     * @param array $options
     * @return string Path to generated video
     */
    public function textToVideo(string $prompt, array $options = []): string;

    /**
     * Generate video from image input.
     *
     * @param string|array $imagePath Single path or array of paths
     * @param array $options
     * @return string Path to generated video
     */
    public function imageToVideo($imagePath, array $options = []): string;

    /**
     * Generate structured scenes for a script.
     *
     * @param string $script
     * @param array $options
     * @return array Array of scene objects/arrays
     */
    public function generateScenes(string $script, array $options = []): array;

    /**
     * Generate voice audio from text.
     *
     * @param string $text
     * @param string $voiceId
     * @param array $options
     * @return string Path to generated audio file
     */
    public function generateVoice(string $text, string $voiceId, array $options = []): string;

    /**
     * Estimate cost for an operation.
     *
     * @param string $type Operation type (text-to-video, image-to-video, scene-planning, voice)
     * @param array $params Parameters for cost estimation
     * @return float Estimated cost in USD or credits
     */
    public function estimateCost(string $type, array $params = []): float;
}
