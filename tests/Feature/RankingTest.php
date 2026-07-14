<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranking_page_can_be_displayed(): void
    {
        $this->seed();

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSee('ランキング');
    }

    public function test_books_are_displayed_in_rating_ranking_order(): void
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'ranking@example.com',
            'password' => 'password',
        ]);

        $highRatedBook = Book::create([
            'user_id' => $user->id,
            'title' => '高評価テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000002000',
            'published_date' => '2026-07-08',
            'description' => '高評価ランキング確認用の書籍です。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=high',
        ]);

        $lowRatedBook = Book::create([
            'user_id' => $user->id,
            'title' => '低評価テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000003000',
            'published_date' => '2026-07-08',
            'description' => '低評価ランキング確認用の書籍です。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=low',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $highRatedBook->id,
            'rating' => 5,
            'comment' => '高評価レビューです。',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $lowRatedBook->id,
            'rating' => 1,
            'comment' => '低評価レビューです。',
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSee('高評価テスト書籍');
        $response->assertSee('低評価テスト書籍');
        $response->assertSeeInOrder([
            '高評価テスト書籍',
            '低評価テスト書籍',
        ]);
    }
}
