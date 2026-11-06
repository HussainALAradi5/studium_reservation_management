<?php

declare(strict_types=1);

namespace App\Controllers\Reviews;

use App\Controllers\Base\Controller;
use App\Dtos\Reviews\ReviewFormDto;
use App\Models\Reviews\Review;
use App\Services\Reviews\ReviewService;
use App\Services\Stadiums\StadiumService;
use App\Utilities\Http\Request;

/** Stadium reviews. Reading needs a sign-in; writing needs reviews.write plus a past rental (checked in the service). */
final class ReviewController extends Controller
{
    /** GET /api/stadiums/{code}/reviews?page= */
    public function index(Request $request): void
    {
        $stadium = (new StadiumService())->findVisible($this->user(), $request->param('code'));
        $page = (new ReviewService())->list($stadium, (int) $request->query('page', 1));
        $this->json(array_map(static fn (Review $review) => $review->toArray(), $page->items), 200, $page->meta());
    }

    /** POST /api/stadiums/{code}/reviews — once per customer and stadium. */
    public function store(Request $request): void
    {
        $user = $this->user();
        $stadium = (new StadiumService())->findVisible($user, $request->param('code'));
        (new ReviewService())->create($user, $stadium, ReviewFormDto::fromInput($request->all()));
        $this->done('Thanks for rating ' . $stadium->name . '!', '/stadiums/' . $stadium->code . '#reviews', 201);
    }

    public function destroy(Request $request): void
    {
        (new ReviewService())->delete($this->user(), $request->param('code'));
        $this->done('Review removed.');
    }
}
