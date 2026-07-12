<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all()->keyBy('email');
        $books = Book::all()->keyBy('isbn');

        $reviews = [
            // 吾輩は猫である
            ['isbn' => '9784101010014', 'email' => 'yamada@example.com', 'rating' => 5, 'comment' => '文章の雰囲気が印象的で、最後まで楽しく読めました。'],
            ['isbn' => '9784101010014', 'email' => 'suzuki@example.com', 'rating' => 4, 'comment' => '登場人物の描写が面白く、時代背景も感じられました。'],
            ['isbn' => '9784101010014', 'email' => 'tanaka@example.com', 'rating' => 4, 'comment' => '独特の語り口が読みごたえのある作品でした。'],

            // 人を動かす
            ['isbn' => '9784422100524', 'email' => 'suzuki@example.com', 'rating' => 5, 'comment' => '人との関わり方を見直すきっかけになりました。'],
            ['isbn' => '9784422100524', 'email' => 'tanaka@example.com', 'rating' => 4, 'comment' => '実生活でも意識したい内容が多くありました。'],
            ['isbn' => '9784422100524', 'email' => 'sato@example.com', 'rating' => 5, 'comment' => '具体例が多く、内容を理解しやすかったです。'],

            // リーダブルコード
            ['isbn' => '9784873115658', 'email' => 'tanaka@example.com', 'rating' => 5, 'comment' => 'コードを書く時の考え方を学べる良い本でした。'],
            ['isbn' => '9784873115658', 'email' => 'sato@example.com', 'rating' => 4, 'comment' => '実務でも役立ちそうな考え方が多くありました。'],
            ['isbn' => '9784873115658', 'email' => 'takahashi@example.com', 'rating' => 5, 'comment' => '読みやすいコードを意識するきっかけになりました。'],

            // 7つの習慣
            ['isbn' => '9784863940246', 'email' => 'yamada@example.com', 'rating' => 4, 'comment' => '考え方を整理するうえで参考になりました。'],
            ['isbn' => '9784863940246', 'email' => 'sato@example.com', 'rating' => 5, 'comment' => '日々の行動を見直すきっかけになる内容でした。'],
            ['isbn' => '9784863940246', 'email' => 'takahashi@example.com', 'rating' => 4, 'comment' => '何度も読み返したい内容だと感じました。'],

            // 坊っちゃん
            ['isbn' => '9784101010021', 'email' => 'yamada@example.com', 'rating' => 4, 'comment' => 'テンポが良く、読みやすい作品でした。'],
            ['isbn' => '9784101010021', 'email' => 'suzuki@example.com', 'rating' => 4, 'comment' => '主人公のまっすぐさが印象に残りました。'],
            ['isbn' => '9784101010021', 'email' => 'takahashi@example.com', 'rating' => 3, 'comment' => '古典作品として楽しめる内容でした。'],

            // サピエンス全史
            ['isbn' => '9784309226712', 'email' => 'yamada@example.com', 'rating' => 5, 'comment' => '人類の歴史を大きな視点で学べました。'],
            ['isbn' => '9784309226712', 'email' => 'tanaka@example.com', 'rating' => 5, 'comment' => '知的好奇心を刺激される内容でした。'],
            ['isbn' => '9784309226712', 'email' => 'sato@example.com', 'rating' => 4, 'comment' => '読みごたえがあり、考えさせられる本でした。'],

            // Clean Code
            ['isbn' => '9784048930598', 'email' => 'suzuki@example.com', 'rating' => 4, 'comment' => '保守しやすいコードについて学べました。'],
            ['isbn' => '9784048930598', 'email' => 'tanaka@example.com', 'rating' => 5, 'comment' => '開発者として参考になる内容が多かったです。'],
            ['isbn' => '9784048930598', 'email' => 'takahashi@example.com', 'rating' => 5, 'comment' => 'コード品質を意識するきっかけになりました。'],

            // 嫌われる勇気
            ['isbn' => '9784478025819', 'email' => 'yamada@example.com', 'rating' => 4, 'comment' => '物事の捉え方を変えるきっかけになりました。'],
            ['isbn' => '9784478025819', 'email' => 'suzuki@example.com', 'rating' => 5, 'comment' => '対話形式で読みやすく、内容も印象に残りました。'],
            ['isbn' => '9784478025819', 'email' => 'sato@example.com', 'rating' => 4, 'comment' => '自分の考え方を見直す参考になりました。'],

            // 火花
            ['isbn' => '9784163902302', 'email' => 'tanaka@example.com', 'rating' => 4, 'comment' => '登場人物の感情描写が印象的でした。'],
            ['isbn' => '9784163902302', 'email' => 'sato@example.com', 'rating' => 3, 'comment' => '独特の雰囲気がある作品でした。'],
            ['isbn' => '9784163902302', 'email' => 'takahashi@example.com', 'rating' => 4, 'comment' => '芸人の世界観が丁寧に描かれていました。'],

            // FACTFULNESS
            ['isbn' => '9784822289607', 'email' => 'yamada@example.com', 'rating' => 5, 'comment' => 'データを見る視点を学べる本でした。'],
            ['isbn' => '9784822289607', 'email' => 'suzuki@example.com', 'rating' => 5, 'comment' => '思い込みに気づくきっかけになりました。'],
            ['isbn' => '9784822289607', 'email' => 'takahashi@example.com', 'rating' => 4, 'comment' => '世界の見方を広げてくれる内容でした。'],

            // コンテナ物語
            ['isbn' => '9784822251468', 'email' => 'tanaka@example.com', 'rating' => 4, 'comment' => '物流の歴史を知ることができて面白かったです。'],
            ['isbn' => '9784822251468', 'email' => 'sato@example.com', 'rating' => 4, 'comment' => '身近な仕組みの背景を学べる内容でした。'],
        ];

        foreach ($reviews as $review) {
            Review::create([
                'user_id' => $users[$review['email']]->id,
                'book_id' => $books[$review['isbn']]->id,
                'rating' => $review['rating'],
                'comment' => $review['comment'],
            ]);
        }
    }
}
