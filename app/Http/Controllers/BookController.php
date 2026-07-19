<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $genres = Genre::orderBy('name')->get();

        $query = Book::with('genres')
            ->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($query) use ($request) {
                $query->where('genres.id', $request->input('genre'));
            });
        }

        match ($request->input('sort', 'newest')) {
            'oldest' => $query->oldest(),
            'rating' => $query->orderByDesc('reviews_avg_rating')->latest(),
            'title' => $query->orderBy('title'),
            default => $query->latest(),
        };

        $books = $query->paginate(10)->withQueryString();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookRequest $request)
    {
        $validated = $request->validated();

        $genreIds = $validated['genres'];
        unset($validated['genres']);

        $validated['user_id'] = auth()->id();

        $book = Book::create($validated);

        $book->genres()->sync($genreIds);

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews.user',
            'reviews.likedByUsers',
        ]);

        if (auth()->check()) {
            auth()->user()->load([
                'favoriteBooks',
                'likedReviews',
            ]);
        }

        return view('books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $book->load('genres');
        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $genreIds = $validated['genres'];
        unset($validated['genres']);

        $book->update($validated);

        $book->genres()->sync($genreIds);

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }

    public function fetchByIsbn(string $isbn)
    {
        if (! preg_match('/\A\d{13}\z/', $isbn)) {
            return response()->json([
                'error' => 'ISBNは13桁で指定してください。',
            ], 422);
        }

        $response = Http::timeout(5)->get('https://www.googleapis.com/books/v1/volumes', array_filter([
            'q' => "isbn:{$isbn}",
            'maxResults' => 1,
            'key' => config('services.google_books.api_key'),
        ]));

        if ($response->status() === 429) {
            return response()->json([
                'error' => 'Google Books APIの利用上限に達しているため、書籍情報を取得できませんでした。時間をおいて再度お試しください。',
            ], 429);
        }

        if (! $response->successful()) {
            return response()->json([
                'error' => '書籍情報の取得に失敗しました。',
            ], 502);
        }

        $items = $response->json('items', []);

        if (empty($items)) {
            return response()->json([
                'error' => '該当する書籍が見つかりませんでした。',
            ], 404);
        }

        $volumeInfo = $items[0]['volumeInfo'] ?? [];

        return response()->json([
            'title' => $volumeInfo['title'] ?? '',
            'author' => implode('、', $volumeInfo['authors'] ?? []),
            'published_date' => $this->normalizePublishedDate($volumeInfo['publishedDate'] ?? null),
            'description' => $volumeInfo['description'] ?? '',
            'image_url' => $volumeInfo['imageLinks']['thumbnail']
                ?? $volumeInfo['imageLinks']['smallThumbnail']
                ?? '',
        ]);
    }

    private function normalizePublishedDate(?string $publishedDate): ?string
    {
        if ($publishedDate === null) {
            return null;
        }

        if (preg_match('/\A\d{4}\z/', $publishedDate)) {
            return "{$publishedDate}-01-01";
        }

        if (preg_match('/\A\d{4}-\d{2}\z/', $publishedDate)) {
            return "{$publishedDate}-01";
        }

        if (preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $publishedDate)) {
            return $publishedDate;
        }

        return null;
    }
}
