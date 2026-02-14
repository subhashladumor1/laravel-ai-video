# Laravel AI Video – Multi-Model Text to Video & Image to Video Generator for Laravel AI SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/subhashladumor1/laravel-ai-video.svg?style=flat-square)](https://packagist.org/packages/subhashladumor1/laravel-ai-video)
[![Total Downloads](https://img.shields.io/packagist/dt/subhashladumor1/laravel-ai-video.svg?style=flat-square)](https://packagist.org/packages/subhashladumor1/laravel-ai-video)

A complete, production-ready Laravel package for generated AI videos using multiple models (OpenAI, Stability, Runway, Pika). Features a robust driver architecture, queue-based rendering, and seamless FFmpeg integration.

## Features

- **Text → Video**: Turn scripts into cinematic scenes with AI planning, voiceover, and subtitles.
- **Image → Video**: Create motion from static images with zoom/pan and AI motion.
- **Marketing Templates**: Ready-to-use presets for Instagram Reels, YouTube Shorts, and Product Ads.
- **Multi-Model Drivers**:
  - **OpenAI**: Scene planning & TTS.
  - **Stability AI**: SVD Image-to-Video.
  - **Runway**: Gen-2 Video Generation.
  - **Composed**: Local FFmpeg pipeline combining models.
- **High Performance**:
  - Queue-based execution.
  - Temporary file streaming (no RAM bloat).
  - Async video polling.
- **Advanced Controls**: FPS, Bitrate, Watermarking, Transitions.
- **AI Guard Integration**: Cost estimation & budget enforcement before generation.

## Installation

```bash
composer require subhashladumor1/laravel-ai-video
php artisan vendor:publish --tag=ai-video-config
```

Ensure FFmpeg is installed on your server:

```bash
sudo apt install ffmpeg
```

## Configuration

Edit `config/ai-video.php` to set your API keys and defaults.

```php
'default_driver' => 'composed',

'drivers' => [
    'openai' => ['api_key' => env('OPENAI_API_KEY')],
    'stability' => ['api_key' => env('STABILITY_API_KEY')],
    // ...
],
```

## Usage

### Text to Video (Composed Pipeline)

```php
use Subhashladumor1\LaravelAiVideo\Facades\AiVideo;
use Subhashladumor1\LaravelAiVideo\Jobs\ProcessVideoJob;

// Synchronous (not recommended for long videos)
$path = AiVideo::driver('composed')->textToVideo(
    "A futuristic city with flying cars and neon lights.",
    ['duration' => 15, 'template' => 'youtube-short']
);

// Asynchronous (Queue)
ProcessVideoJob::dispatch(
    'text-to-video',
    "Create a promo for our new coffee brand.",
    ['template' => 'instagram-reel'],
    'composed'
);
```

### Image to Video

```php
$path = AiVideo::driver('stability')->imageToVideo(
    public_path('images/hero.jpg'),
    ['motion_bucket_id' => 127]
);
```

### Scene Planning Only

```php
$scenes = AiVideo::driver('openai')->generateScenes(
    "A detective walks into a dark room. He finds a glowing artifact."
);
```

### Marketing Templates

Templates define resolution, fonts, and style.

- `instagram-reel` (9:16)
- `youtube-short` (9:16)
- `product-ad` (1:1)

## AI Guard Integration

This package integrates with `laravel-ai-guard` to prevent budget overruns.

- **Cost Estimation**: Checks estimated cost against budget before dispatching jobs.
- **Usage Logging**: Logs actual token/credit usage post-generation.

## Architecture

- **VideoManager**: Orchestrate drivers.
- **ScenePlanner**: Smart script breakdown.
- **VoiceGenerator**: TTS integration.
- **VideoRenderer**: FFmpeg wrapper for merging and effects.

## License

MIT
