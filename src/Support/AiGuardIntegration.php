<?php

namespace Subhashladumor1\LaravelAiVideo\Support;

use Illuminate\Support\Facades\Log;

/**
 * Proxy for interacting with the optional Laravel AI Guard package.
 */
class AiGuardIntegration
{
    /**
     * Check if the guard package is installed and enabled.
     */
    public static function isAvailable(): bool
    {
        return class_exists(\Subhashladumor1\LaravelAiGuard\Facades\AiGuard::class) &&
            config('ai-video.guard.enabled', true);
    }

    /**
     * Estimate and check cost before execution.
     * 
     * @param float $estimatedCost Estimated cost in USD/credits.
     * @param string $driver Driver name.
     * @throws \Exception If budget exceeded.
     */
    public static function checkBudget(float $estimatedCost, string $driver): void
    {
        if (!self::isAvailable()) {
            return;
        }

        try {
            // Using the Facade method assuming specific signature
            // Adjust based on actual package API if known, otherwise generic call
            \Subhashladumor1\LaravelAiGuard\Facades\AiGuard::checkBudget($estimatedCost, $driver);
        } catch (\Exception $e) {
            // If strictly budget related, escalate exception
            throw $e;
        }
    }

    /**
     * Log the usage after execution.
     *
     * @param float $cost Actual cost incurred.
     * @param string $driver Driver name.
     * @param array $meta Additional metadata.
     */
    public static function logUsage(float $cost, string $driver, array $meta = []): void
    {
        if (!self::isAvailable()) {
            return;
        }

        try {
            \Subhashladumor1\LaravelAiGuard\Facades\AiGuard::logUsage($cost, $driver, $meta);
        } catch (\Exception $e) {
            Log::warning("AI Guard implementation failed to log usage: " . $e->getMessage());
        }
    }
}
