<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Media Rendition Profiles (Locked Allow-list)
    |--------------------------------------------------------------------------
    | We generate deterministic derivatives from the original media asset.
    | Profiles are stable and should not be expanded casually, to avoid cache
    | explosion and inconsistent frontend usage.
    |
    | Strategy:
    | - Resize by width (preserve aspect ratio)
    | - No crop for fashion imagery (prevents losing details)
    | - Output format: webp (recommended) OR jpeg (safe default)
    */

    'renditions' => [

        'thumb_sm' => [
            'width' => 120,
            'format' => 'webp',
            'quality' => 82,
        ],

        'gallery_thumb' => [
            'width' => 160,
            'format' => 'webp',
            'quality' => 82,
        ],

        'plp_480w' => [
            'width' => 480,
            'format' => 'webp',
            'quality' => 84,
        ],

        'pdp_1200w' => [
            'width' => 1200,
            'format' => 'webp',
            'quality' => 86,
        ],
    ],

];
