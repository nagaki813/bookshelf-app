<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_books_index_can_be_displayed(): void
    {
        $this->seed();

        $response = $this->getJson(route('api.books.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => '吾輩は猫である',
        ]);
    }

    public function test_api_book_detail_can_be_displayed(): void
    {
        $this->seed();

        $book = Book::first();

        $response = $this->getJson(route('api.books.show', $book));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => $book->title,
            'author' => $book->author,
        ]);
    }

    public function test_api_book_can_be_created(): void
    {
        $this->seed();

        $user = User::first();
        $genre = Genre::where('name', '小説')->first();

        $response = $this->postJson(route('api.books.store'), [
            'user_id' => $user->id,
            'title' => 'API登録テスト書籍',
            'author' => 'APIテスト著者',
            'isbn' => '9784000004000',
            'published_date' => '2026-07-08',
            'description' => 'API登録テスト用の説明です。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=api',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'title' => 'API登録テスト書籍',
            'author' => 'APIテスト著者',
            'isbn' => '9784000004000',
        ]);

        $book = Book::where('isbn', '9784000004000')->first();

        $this->assertNotNull($book);

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'API登録テスト書籍',
            'isbn' => '9784000004000',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_api_book_create_validation_errors_are_returned(): void
    {
        $this->seed();

        $response = $this->postJson(route('api.books.store'), [
            'user_id' => '',
            'title' => '',
            'author' => '',
            'isbn' => '123',
            'published_date' => '',
            'description' => '',
            'image_url' => 'invalid-url',
            'genres' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'user_id',
            'title',
            'author',
            'isbn',
            'published_date',
            'image_url',
            'genres',
        ]);
    }

    public function test_api_book_can_be_updated(): void
    {
        $this->seed();

        $book = Book::first();
        $user = $book->user;
        $businessGenre = Genre::where('name', 'ビジネス')->first();

        $response = $this->putJson(route('api.books.update', $book), [
            'user_id' => $user->id,
            'title' => 'API更新後タイトル',
            'author' => 'API更新後著者',
            'isbn' => $book->isbn,
            'published_date' => '2026-07-08',
            'description' => 'API更新後の説明です。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=api-update',
            'genres' => [$businessGenre->id],
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => 'API更新後タイトル',
            'author' => 'API更新後著者',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'API更新後タイトル',
            'author' => 'API更新後著者',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $businessGenre->id,
        ]);
    }

    public function test_api_book_can_be_deleted(): void
    {
        $this->seed();

        $book = Book::first();

        $response = $this->deleteJson(route('api.books.destroy', $book));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }
}
