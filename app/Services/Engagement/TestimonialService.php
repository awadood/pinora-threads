<?php

namespace App\Services\Engagement;

use App\Models\Testimonial;
use App\Repositories\Engagement\Contracts\ITestimonialRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * TestimonialService
 *
 * Encapsulates testimonial business rules: publishing and moderation.
 *
 * @author Abdul Wadood
 */
class TestimonialService
{
    public function __construct(
        protected ITestimonialRepository $testimonials
    ) {}

    /**
     * Public listing of testimonials for storefront.
     *
     * @return Collection<int, Testimonial>
     */
    public function getPublicTestimonials(): Collection
    {
        return $this->testimonials->getPublicTestimonials();
    }

    /**
     * Admin create.
     *
     * @param  array<string,mixed>  $data
     */
    public function create(array $data, ?int $reviewerId = null): Testimonial
    {
        $data = $this->applyReviewMetadata(null, $data, $reviewerId);

        /** @var Testimonial $testimonial */
        $testimonial = $this->testimonials->create($data);

        return $testimonial;
    }

    /**
     * Admin update.
     *
     * @param  array<string,mixed>  $data
     */
    public function update(Testimonial $testimonial, array $data, ?int $reviewerId = null): Testimonial
    {
        $originalStatus = $testimonial->status ?? null;

        $data = $this->applyReviewMetadata($originalStatus, $data, $reviewerId);

        $testimonial->fill($data);
        $testimonial->save();

        return $testimonial;
    }

    public function delete(Testimonial $testimonial): void
    {
        $testimonial->delete();
    }

    /**
     * Apply reviewer and publish timestamps when status changes.
     *
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    protected function applyReviewMetadata(?string $originalStatus, array $data, ?int $reviewerId): array
    {
        $newStatus = $data['status'] ?? $originalStatus;

        if ($reviewerId !== null && $newStatus !== null && $newStatus !== 'pending') {
            $data['reviewed_by'] = $reviewerId;

            if ($newStatus === 'approved' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }
        }

        return $data;
    }
}
