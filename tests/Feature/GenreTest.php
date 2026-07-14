<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_genres_index(): void
    {
        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertSee('小説');
    }

    public function test_guest_cannot_view_genres_index(): void
    {
        $this->seed();

        $response = $this->get(route('genres.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_genre(): void
    {
        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => 'テストジャンル',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('genres', [
            'name' => 'テストジャンル',
        ]);
    }

    public function test_genre_create_validation_errors_are_displayed(): void
    {
        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_duplicate_genre_name_cannot_be_created(): void
    {
        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => '小説',
        ]);

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_authenticated_user_can_view_genre_detail(): void
    {
        $this->seed();

        $user = User::first();
        $genre = Genre::where('name', '小説')->first();

        $response = $this->actingAs($user)->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertSee('小説');
    }

    public function test_authenticated_user_can_update_genre(): void
    {
        $this->seed();

        $user = User::first();
        $genre = Genre::where('name', '料理')->first();

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => '料理・レシピ',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '料理・レシピ',
        ]);
    }

    public function test_genre_update_validation_errors_are_displayed(): void
    {
        $this->seed();

        $user = User::first();
        $genre = Genre::where('name', '料理')->first();

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $this->seed();

        $user = User::first();
        $genre = Genre::whereHas('books')->first();

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_genre_without_books_can_be_deleted(): void
    {
        $this->seed();

        $user = User::first();

        $genre = Genre::create([
            'name' => '削除用ジャンル',
        ]);

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }
}
