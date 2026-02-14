<?php

namespace Subhashladumor1\LaravelAiVideo\Templates;

class InstagramReelTemplate implements Template
{
    public function name(): string
    {
        return 'instagram-reel';
    }

    public function aspectRatio(): string
    {
        return '9:16';
    }

    public function width(): int
    {
        return 1080;
    }

    public function height(): int
    {
        return 1920;
    }

    public function defaultDuration(): int
    {
        return 15;
    }

    public function fontClient(): string
    {
        return 'Inter-Bold';
    }

    public function musicPath(): ?string
    {
        return storage_path('ai-video/assets/music/upbeat-reel.mp3');
    }

    public function applyEffect(string $videoPath): string
    {
        // Add specific overlay or filter
        return $videoPath;
    }
}
