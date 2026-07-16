<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReadingPlanController extends Controller
{
    public function index(Request $request)
    {
        $currentStatus = $request->query('status');

        $readingPlans = ReadingPlan::with('book')
            ->where('user_id', auth()->id())
            ->when($currentStatus, fn ($query) => $query->where('status', $currentStatus))
            ->latest()
            ->get();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    public function create()
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => [
                'required',
                'integer',
                'exists:books,id',
                Rule::unique('reading_plans', 'book_id')
                    ->where('user_id', auth()->id()),
            ],
            'target_date' => ['required', 'date'],
        ], [
            'book_id.required' => '書籍を選択してください。',
            'book_id.exists' => '選択された書籍が存在しません。',
            'book_id.unique' => 'この書籍の読書計画は既に登録されています。',
            'target_date.required' => '期日を入力してください。',
            'target_date.date' => '期日は日付形式で入力してください。',
        ]);

        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $validated['book_id'],
            'due_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::Planned,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を登録しました。');
    }

    public function edit(ReadingPlan $readingPlan)
    {
        $this->authorizeOwner($readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    public function update(Request $request, ReadingPlan $readingPlan)
    {
        $this->authorizeOwner($readingPlan);

        $validated = $request->validate([
            'target_date' => ['required', 'date'],
        ], [
            'target_date.required' => '期日を入力してください。',
            'target_date.date' => '期日は日付形式で入力してください。',
        ]);

        $readingPlan->update([
            'due_date' => $validated['target_date'],
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    public function destroy(ReadingPlan $readingPlan)
    {
        $this->authorizeOwner($readingPlan);

        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    public function complete(ReadingPlan $readingPlan)
    {
        $this->authorizeOwner($readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を読了にしました。');
    }

    private function authorizeOwner(ReadingPlan $readingPlan): void
    {
        abort_unless($readingPlan->user_id === auth()->id(), 403);
    }
}
