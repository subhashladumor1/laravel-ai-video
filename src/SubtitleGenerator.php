<?php

namespace Subhashladumor1\LaravelAiVideo;

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Exception;
use Illuminate\Support\Facades\Storage;

class SubtitleGenerator
{
    public function generate(string $text, float $duration, string $format = 'srt'): string
    {
        // Simple uniform distribution logic for MVP
        // In production, use Whisper for precise alignment

        $lines = explode("\n", wordwrap($text, 40)); // Split long text
        $lineCount = count($lines);
        $durationPerLine = $duration / max(1, $lineCount);

        $content = "";
        $currentTime = 0;

        foreach ($lines as $index => $line) {
            $startTime = $this->formatTime($currentTime, $format);
            $endTime = $this->formatTime($currentTime + $durationPerLine, $format);

            if ($format === 'srt') {
                $content .= ($index + 1) . "\n";
                $content .= "{$startTime} --> {$endTime}\n";
                $content .= trim($line) . "\n\n";
            } else {
                // VTT
                if ($index === 0)
                    $content .= "WEBVTT\n\n";
                $content .= "{$startTime} --> {$endTime}\n";
                $content .= trim($line) . "\n\n";
            }

            $currentTime += $durationPerLine;
        }

        $path = tempnam(sys_get_temp_dir(), 'subs_') . '.' . $format;
        file_put_contents($path, $content);

        return $path;
    }

    protected function formatTime(float $seconds, string $format): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        $millis = round(($seconds - floor($seconds)) * 1000);

        if ($format === 'srt') {
            return sprintf("%02d:%02d:%02d,%03d", $hours, $minutes, $secs, $millis);
        }
        return sprintf("%02d:%02d:%02d.%03d", $hours, $minutes, $secs, $millis);
    }
}
