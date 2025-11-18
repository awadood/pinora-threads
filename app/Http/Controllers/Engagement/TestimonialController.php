<?php

namespace App\Http\Controllers\Engagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engagement\TestimonialRequest;
use App\Http\Resources\Engagement\TestimonialResource;
use App\Models\Testimonial;
use App\Services\Engagement\TestimonialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TestimonialController
 *
 * Public listing of testimonials plus admin CRUD for managing quotes.
 *
 * @author Abdul Wadood
 */
class TestimonialController extends Controller
{
    public function __construct(
        protected TestimonialService $service
    ) {}

    /**
     * Public endpoint — returns only approved & published testimonials
     * ordered by sort_order then published_at desc.
     */
    public function index(Request $request)
    {
        $items = $this->service->getPublicTestimonials();

        return TestimonialResource::collection($items);
    }

    /**
     * Admin-only create.
     */
    public function store(TestimonialRequest $request)
    {
        $testimonial = $this->service->create(
            $request->validated(),
            $request->user()?->getKey()
        );

        return (TestimonialResource::make($testimonial))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Admin-only update.
     */
    public function update(TestimonialRequest $request, Testimonial $testimonial)
    {
        $updated = self::updateModel($this->service, $testimonial, $request);

        return TestimonialResource::make($updated);
    }

    /**
     * Admin-only delete.
     */
    public function destroy(Testimonial $testimonial): JsonResponse
    {
        $this->service->delete($testimonial);

        return response()->json([], 204);
    }

    /**
     * Shared update logic so it can be reused if needed.
     */
    protected static function updateModel(
        TestimonialService $service,
        Testimonial $testimonial,
        TestimonialRequest $request
    ): Testimonial {
        return $service->update(
            $testimonial,
            $request->validated(),
            $request->user()?->getKey()
        );
    }
}
