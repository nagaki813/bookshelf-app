<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_reading_plans_index(): void
    {
        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)->get(route('reading-plans.index'));

        $response->assertStatus(200);
        $response->assertSee('読書計画');
    }

    public function test_guest_cannot_view_reading_plans_index(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_reading_plan_create_page(): void
    {
        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)->get(route('reading-plans.create'));

        $response->assertStatus(200);
        $response->assertSee('新規読書計画作成');
        $response->assertSee('書籍');
    }

    public function test_authenticated_user_can_create_reading_plan(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => ReadingPlanStatus::Planned->value,
        ]);
    }

    public function test_reading_plan_create_validation_errors_are_displayed(): void
    {
        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => '',
            'target_date' => '',
        ]);

        $response->assertSessionHasErrors([
            'book_id',
            'target_date',
        ]);
    }

    public function test_same_book_cannot_be_registered_twice_by_same_user(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addDays(10)->toDateString(),
        ]);

        $response->assertSessionHasErrors('book_id');

        $this->assertDatabaseCount('reading_plans', 1);
    }

    public function test_owner_can_access_reading_plan_edit_page(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));

        $response->assertStatus(200);
        $response->assertSee('読書計画編集');
        $response->assertSee($book->title);
    }

    public function test_user_cannot_access_other_users_reading_plan_edit_page(): void
    {
        $this->seed();

        $owner = User::first();
        $otherUser = User::where('id', '!=', $owner->id)->first();
        $book = Book::first();

        $readingPlan = ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $response = $this->actingAs($otherUser)->get(route('reading-plans.edit', $readingPlan));

        $response->assertStatus(403);
    }

    public function test_owner_can_update_reading_plan(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $newDate = now()->addDays(14)->toDateString();

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => $newDate,
        ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'due_date' => $newDate,
        ]);
    }

    public function test_owner_can_complete_reading_plan(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));

        $readingPlan->refresh();

        $this->assertSame(ReadingPlanStatus::Completed, $readingPlan->status);
        $this->assertNotNull($readingPlan->completed_at);
    }

    public function test_owner_can_delete_reading_plan(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }
}
