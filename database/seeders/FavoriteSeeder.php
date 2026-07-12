<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all()->values();
        $books = Book::all()->values();

        $favorites = [
            [0, 0], [0, 1], [0, 2],
            [1, 2], [1, 3], [1, 4],
            [2, 4], [2, 5], [2, 6],
            [3, 6], [3, 7], [3, 8],
            [4, 8], [4, 9], [4, 10],
        ];

        foreach ($favorites as [$userIndex, $bookIndex]) {
            DB::table('favorites')->insert([
                'user_id' => $users[$userIndex]->id,
                'book_id' => $books[$bookIndex]->id,
            ]);
        }
    }
}
