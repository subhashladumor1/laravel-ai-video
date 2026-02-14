<?php

namespace Subhashladumor1\LaravelAiVideo\Facades;

use Illuminate\Support\Facades\Facade;

class AiVideo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ai-video';
    }
}
