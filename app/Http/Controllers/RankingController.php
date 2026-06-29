<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class RankingController extends Controller
{
    public function index()
    {
        $books = Book::with(['genres', 'reviews'])
            ->withCount('reviews')
            ->orderByDesc('reviews_count')
            ->get();

        return view('ranking.index', compact('books'));
    }
}
