<?php

namespace App\Observers;

use App\Jobs\GenerateMediaRenditionsJob;
use App\Models\MediaAsset;

class MediaAssetObserver
{
    public function created(MediaAsset $asset): void
    {
        GenerateMediaRenditionsJob::dispatch($asset->id);
    }
}
