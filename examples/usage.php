<?php

use Subhashladumor1\LaravelAiVideo\Facades\AiVideo;

// Ensure you have loaded the package properly in your Laravel app

// 1. Generate Scene Video (Composed)
$videoPath = AiVideo::driver('composed')->textToVideo(
    "A majestic lion walking through the savanna at subset.",
    ['duration' => 10, 'template' => 'youtube-short']
);

echo "Video Generated at: " . $videoPath . "\n";

// 2. Image to Video (Stability)
$imagePath = __DIR__ . '/sample.jpg';
$videoPath2 = AiVideo::driver('stability')->imageToVideo(
    $imagePath,
    ['motion_bucket_id' => 127]
);

echo "Image Motion Video at: " . $videoPath2 . "\n";

// 3. Queue Processing
dispatch(new \Subhashladumor1\LaravelAiVideo\Jobs\ProcessVideoJob(
    'text-to-video',
    "Create a marketing video for our tech startup.",
    ['template' => 'product-ad'],
    'composed'
));

echo "Job dispatched.\n";
