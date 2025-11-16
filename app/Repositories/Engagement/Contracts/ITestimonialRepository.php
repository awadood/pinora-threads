<?php

namespace App\Repositories\Engagement\Contracts;

use App\Models\Testimonial;
use App\Repositories\IBaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * ITestimonialRepository
 *
 * Repository contract for managing testimonials.
 *
 * @author Abdul Wadood
 */
interface ITestimonialRepository extends IBaseRepository
{
    /**
     * Return testimonials which are approved and published,
     * ordered by sort_order asc then published_at desc.
     *
     * @return Collection<int, Testimonial>
     */
    public function getPublicTestimonials(): Collection;
}
