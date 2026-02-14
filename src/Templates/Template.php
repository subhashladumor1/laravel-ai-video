<?php

namespace Subhashladumor1\LaravelAiVideo\Templates;

interface Template
{
    public function name(): string;
    public function aspectRatio(): string; // 9:16, 16:9, 1:1, 4:5
    public function width(): int;
    public function height(): int;
    public function defaultDuration(): int;
    public function fontClient(): string; // font path or name
    public function musicPath(): ?string;
    public function applyEffect(string $videoPath): string; // Apply template specific effects
}
