<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_like_review(): void
    {
        $this->seed();

        $user = User::first();
        $review = Review::whereDoesntHave('likedByUsers', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->first();

        $response = $this
            ->from(route('books.show', $review->book))
            ->actingAs($user)
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book));

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_authenticated_user_can_unlike_review(): void
    {
        $this->seed();

        $user = User::first();
        $review = Review::first();

        $user->likedReviews()->syncWithoutDetaching([$review->id]);

        $response = $this
            ->from(route('books.show', $review->book))
            ->actingAs($user)
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book));

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_guest_cannot_like_review(): void
    {
        $this->seed();

        $review = Review::first();

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect(route('login'));
    }
}
