<?php

return [
    'default_driver' => env('AI_VIDEO_DRIVER', 'composed'),

    // Set to true to attempt using shared clients from 'subhashladumor1/laravel-ai-sdk'
    'use_sdk' => env('AI_VIDEO_USE_SDK', false),

    'drivers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => 'gpt-4o', // Updated to latest flagship model
            'voice_model' => 'tts-1',
            'image_model' => 'dall-e-3',
            'image_size' => '1024x1024',
        ],
        'stability' => [
            'api_key' => env('STABILITY_API_KEY'),
            'image_model' => 'ultra', // Stable Diffusion Ultra if available or distinct from video
            'video_model' => 'stable-video-diffusion-img2vid-xt', // XT for longer/better clips
        ],
        'runway' => [
            'api_key' => env('RUNWAY_API_KEY'),
            'model' => 'gen-3-alpha', // Latest Gen-3 Alpha if accessible via API (or fallback to gen-2)
        ],
        'pika' => [
            'api_key' => env('PIKA_API_KEY'),
        ],
        'composed' => [
            // Orchestration settings
            'concurrency' => 2, // How many scenes to generate in parallel (if implemented)
        ],
    ],

    'storage_path' => storage_path('app/public/ai-videos'),
    'temp_path' => storage_path('app/temp/ai-videos'),

    'ffmpeg' => [
        'path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
        'probe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),
        'threads' => 4,
        'timeout' => 3600,
    ],

    'defaults' => [
        'template' => 'youtube-short',
        'fps' => 30,
        'duration_per_scene' => 5, // seconds
        'max_duration' => 60, // seconds constraint
    ],

    'templates' => [
        // Register custom templates here
    ],

    // AI Guard Settings
    'guard' => [
        'enabled' => env('AI_VIDEO_GUARD_ENABLED', true),
        'cost_limit_per_video' => 10.00, // Adjusted for higher quality models
    ],
];
