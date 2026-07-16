<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Review;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $reviews = Review::where('user_id', $user->id);

        $ratingDistribution = collect(range(1, 5))
            ->map(fn (int $rating) => (clone $reviews)->where('rating', $rating)->count());

        $topRatedBooks = Review::with('book')
            ->where('user_id', $user->id)
            ->where('rating', '>=', 4)
            ->orderByDesc('rating')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Review $review) => [
                'id' => $review->book->id,
                'title' => $review->book->title,
                'author' => $review->book->author,
                'rating' => $review->rating,
            ]);

        $genreRatings = Genre::query()
            ->select('genres.id', 'genres.name')
            ->selectRaw('AVG(reviews.rating) as average_rating')
            ->selectRaw('COUNT(reviews.id) as count')
            ->join('book_genre', 'genres.id', '=', 'book_genre.genre_id')
            ->join('books', 'book_genre.book_id', '=', 'books.id')
            ->join('reviews', 'books.id', '=', 'reviews.book_id')
            ->where('reviews.user_id', $user->id)
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('average_rating')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn (Genre $genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
                'average_rating' => (float) $genre->average_rating,
                'count' => (int) $genre->count,
            ]);

        $stats = [
            'summary' => [
                'total_reviews' => (clone $reviews)->count(),
                'books_read' => (clone $reviews)->distinct('book_id')->count('book_id'),
                'average_rating' => (float) ((clone $reviews)->avg('rating') ?? 0),
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
