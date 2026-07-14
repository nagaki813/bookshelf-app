<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_favorites_page(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $user->favoriteBooks()->syncWithoutDetaching([$book->id]);

        $response = $this->actingAs($user)->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertSee($book->title);
    }

    public function test_guest_cannot_view_favorites_page(): void
    {
        $this->seed();

        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_add_book_to_favorites(): void
    {
        $this->seed();

        $user = User::first();

        $book = Book::whereDoesntHave('favoritedByUsers', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->first();

        $response = $this
            ->from(route('books.show', $book))
            ->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_authenticated_user_can_remove_book_from_favorites(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $user->favoriteBooks()->syncWithoutDetaching([$book->id]);

        $response = $this
            ->from(route('books.show', $book))
            ->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_guest_cannot_toggle_favorite(): void
    {
        $this->seed();

        $book = Book::first();

        $response = $this->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('login'));
    }
}
