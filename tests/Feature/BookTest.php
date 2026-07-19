<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_index_can_be_displayed(): void
    {
        $this->seed();

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertSee('書籍一覧');
        $response->assertSee('人を動かす');
    }

    public function test_book_detail_can_be_displayed(): void
    {
        $this->seed();

        $book = Book::first();

        $response = $this->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee($book->title);
        $response->assertSee($book->author);
    }

    public function test_authenticated_user_can_access_book_create_page(): void
    {
        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)->get(route('books.create'));

        $response->assertStatus(200);
        $response->assertSee('書籍');
    }

    public function test_guest_cannot_access_book_create_page(): void
    {
        $this->seed();

        $response = $this->get(route('books.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_book(): void
    {
        $this->seed();

        $user = User::first();
        $genre = Genre::where('name', '小説')->first();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000001000',
            'published_date' => '2026-07-08',
            'description' => 'テスト用の書籍説明です。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=test',
            'genres' => [$genre->id],
        ]);

        $book = Book::where('isbn', '9784000001000')->first();

        $this->assertNotNull($book);
        $this->assertSame($user->id, $book->user_id);
        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000001000',
        ]);
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        $response->assertRedirect(route('books.show', $book));
    }

    public function test_book_create_validation_errors_are_displayed(): void
    {
        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => '',
            'author' => '',
            'isbn' => '123',
            'published_date' => '',
            'description' => '',
            'image_url' => 'invalid-url',
            'genres' => [],
        ]);

        $response->assertSessionHasErrors([
            'title',
            'author',
            'isbn',
            'image_url',
            'genres',
        ]);
    }

    public function test_owner_can_update_book(): void
    {
        $this->seed();

        $book = Book::first();
        $user = $book->user;

        $businessGenre = Genre::where('name', 'ビジネス')->first();
        $selfHelpGenre = Genre::where('name', '自己啓発')->first();

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => $book->isbn,
            'published_date' => '2026-07-08',
            'description' => '更新後の説明です。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=update',
            'genres' => [$businessGenre->id, $selfHelpGenre->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => $book->isbn,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $businessGenre->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $selfHelpGenre->id,
        ]);
    }

    public function test_user_cannot_update_other_users_book(): void
    {
        $this->seed();

        $book = Book::first();
        $owner = $book->user;
        $otherUser = User::where('id', '!=', $owner->id)->first();
        $genre = Genre::where('name', '小説')->first();

        $response = $this->actingAs($otherUser)->put(route('books.update', $book), [
            'title' => '不正更新',
            'author' => '不正著者',
            'isbn' => $book->isbn,
            'published_date' => '2026-07-08',
            'description' => '不正な説明です。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=invalid',
            'genres' => [$genre->id],
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
            'title' => '不正更新',
        ]);
    }

    public function test_owner_can_delete_book(): void
    {
        $this->seed();

        $book = Book::first();
        $user = $book->user;

        $response = $this->actingAs($user)->delete(route('books.destroy', $book));

        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_book(): void
    {
        $this->seed();

        $owner = User::first();
        $otherUser = User::where('id', '!=', $owner->id)->first();
        $book = Book::first();

        $response = $this->actingAs($otherUser)->delete(route('books.destroy', $book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    public function test_authenticated_user_can_create_book_without_isbn_and_published_date(): void
    {
        $this->seed();

        $user = User::first();
        $genre = Genre::first();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'ISBNなしテスト書籍',
            'author' => 'テスト著者',
            'isbn' => '',
            'published_date' => '',
            'description' => 'ISBNと出版日なしで登録するテストです。',
            'image_url' => '',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => 'ISBNなしテスト書籍',
            'author' => 'テスト著者',
            'isbn' => null,
            'published_date' => null,
        ]);
    }

    public function test_books_can_be_searched_by_keyword(): void
    {
        $this->seed();

        $response = $this->get(route('books.index', [
            'keyword' => 'リーダブル',
        ]));

        $response->assertStatus(200);
        $response->assertSee('リーダブルコード');
        $response->assertDontSee('吾輩は猫である');
    }

    public function test_books_can_be_filtered_by_genre(): void
    {
        $this->seed();

        $genre = Genre::where('name', '技術書')->first();

        $response = $this->get(route('books.index', [
            'genre' => $genre->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('リーダブルコード');
        $response->assertSee('Clean Code');
        $response->assertDontSee('吾輩は猫である');
    }

    public function test_authenticated_user_can_fetch_book_information_by_isbn(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'API取得テスト書籍',
                            'authors' => ['テスト著者A', 'テスト著者B'],
                            'publishedDate' => '2020',
                            'description' => 'Google Books APIから取得した説明です。',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/book.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)
            ->getJson(route('books.fetch-by-isbn', '9784000000000'));

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'API取得テスト書籍',
                'author' => 'テスト著者A、テスト著者B',
                'published_date' => '2020-01-01',
                'description' => 'Google Books APIから取得した説明です。',
                'image_url' => 'https://example.com/book.jpg',
            ]);
    }

    public function test_guest_cannot_fetch_book_information_by_isbn(): void
    {
        $response = $this->getJson(route('books.fetch-by-isbn', '9784000000000'));

        $response->assertStatus(401);
    }
}
