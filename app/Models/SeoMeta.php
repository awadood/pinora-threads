<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'meta_title',
        'meta_description',
        'meta_robots',
        'canonical_url',

        'og_title',
        'og_description',
        'og_type',
        'og_url',
        'og_image_id',

        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image_id',

        'schema_type',
        'schema_payload',
        'extra',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schema_payload' => 'array',
            'extra' => 'array',
        ];
    }

    // Lifecycle

    // Relationships
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function ogImage()
    {
        return $this->belongsTo(MediaAsset::class, 'og_image_id');
    }

    public function twitterImage()
    {
        return $this->belongsTo(MediaAsset::class, 'twitter_image_id');
    }
}
