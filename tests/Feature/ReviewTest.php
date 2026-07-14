<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_review(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 5,
            'comment' => 'とても良い本でした。',
        ]);

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても良い本でした。',
        ]);
    }

    public function test_guest_cannot_create_review(): void
    {
        $this->seed();

        $book = Book::first();

        $response = $this->post(route('reviews.store', $book), [
            'rating' => 5,
            'comment' => '未ログイン投稿です。',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('reviews', [
            'comment' => '未ログイン投稿です。',
        ]);
    }

    public function test_review_create_validation_errors_are_displayed(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => '',
            'comment' => '',
        ]);

        $response->assertSessionHasErrors([
            'rating',
            'comment',
        ]);
    }

    public function test_owner_can_access_review_edit_page(): void
    {
        $this->seed();

        $review = Review::first();
        $user = $review->user;

        $response = $this->actingAs($user)->get(route('reviews.edit', $review));

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_other_users_review_edit_page(): void
    {
        $this->seed();

        $review = Review::first();
        $owner = $review->user;
        $otherUser = User::where('id', '!=', $owner->id)->first();

        $response = $this->actingAs($otherUser)->get(route('reviews.edit', $review));

        $response->assertForbidden();
    }

    public function test_owner_can_update_review(): void
    {
        $this->seed();

        $review = Review::first();
        $user = $review->user;

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 4,
            'comment' => 'レビューを更新しました。',
        ]);

        $response->assertRedirect(route('books.show', $review->book));

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => 'レビューを更新しました。',
        ]);
    }

    public function test_user_cannot_update_other_users_review(): void
    {
        $this->seed();

        $review = Review::first();
        $owner = $review->user;
        $otherUser = User::where('id', '!=', $owner->id)->first();

        $response = $this->actingAs($otherUser)->put(route('reviews.update', $review), [
            'rating' => 1,
            'comment' => '不正なレビュー更新です。',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
            'comment' => '不正なレビュー更新です。',
        ]);
    }

    public function test_owner_can_delete_review(): void
    {
        $this->seed();

        $review = Review::first();
        $book = $review->book;
        $user = $review->user;

        $response = $this->actingAs($user)->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_review(): void
    {
        $this->seed();

        $review = Review::first();
        $owner = $review->user;
        $otherUser = User::where('id', '!=', $owner->id)->first();

        $response = $this->actingAs($otherUser)->delete(route('reviews.destroy', $review));

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}
