<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all()->values();
        $reviews = Review::all()->values();

        $likes = [];

        foreach ($reviews as $reviewIndex => $review) {
            $likeCount = $reviewIndex % 4;

            for ($i = 0; $i < $likeCount; $i++) {
                $user = $users[($reviewIndex + $i) % $users->count()];

                $likes[] = [
                    'user_id' => $user->id,
                    'review_id' => $review->id,
                ];
            }
        }

        foreach (array_slice($likes, 0, 40) as $like) {
            DB::table('review_likes')->insert($like);
        }
    }
}