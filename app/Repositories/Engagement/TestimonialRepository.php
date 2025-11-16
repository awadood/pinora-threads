<?php

namespace App\Repositories\Engagement;

use App\Models\Testimonial;
use App\Repositories\BaseRepository;
use App\Repositories\Engagement\Contracts\ITestimonialRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * TestimonialRepository
 *
 * Eloquent-based repository for testimonials.
 *
 * @author Abdul Wadood
 */
class TestimonialRepository extends BaseRepository implements ITestimonialRepository
{
    protected string $modelClass = Testimonial::class;

    public function getPublicTestimonials(): Collection
    {
        return $this->query()
            ->where('status', 'approved')
            ->whereNotNull('published_at')
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->get();
    }
}
