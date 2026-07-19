<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiBookRequest;
use App\Models\Book;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with(['user:id,name', 'genres:id,name'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->get();

        return response()->json([
            'data' => $books,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApiBookRequest $request)
    {
        $validated = $request->validated();

        $genreIds = $validated['genres'];
        unset($validated['genres']);

        $book = Book::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        $book->genres()->sync($genreIds);

        $book->load(['user:id,name', 'genres:id,name']);

        return response()->json([
            'message' => '書籍を登録しました。',
            'data' => $book,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load(['user:id,name', 'genres:id,name'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return response()->json([
            'data' => $book,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApiBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $genreIds = $validated['genres'];
        unset($validated['genres']);

        $book->update($validated);
        $book->genres()->sync($genreIds);

        $book->load(['user:id,name', 'genres:id,name']);

        return response()->json([
            'message' => '書籍を更新しました。',
            'data' => $book,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json([
            'message' => '書籍を削除しました。',
        ]);
    }
}
