<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_three_days_before_notification(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => today()->addDays(3)->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $this->artisan('reading-plans:check')
            ->assertSuccessful();

        $notification = $user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame('three_days_before', $notification->data['timing']);
        $this->assertSame($book->title, $notification->data['book_title']);
    }

    public function test_command_sends_on_due_date_notification(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => today()->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $this->artisan('reading-plans:check')
            ->assertSuccessful();

        $notification = $user->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame('on_due_date', $notification->data['timing']);
    }

    public function test_command_sends_three_days_after_notification_and_marks_plan_as_expired(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => today()->subDays(3)->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $this->artisan('reading-plans:check')
            ->assertSuccessful();

        $readingPlan->refresh();

        $notification = $user->notifications()->first();

        $this->assertSame(ReadingPlanStatus::Expired, $readingPlan->status);
        $this->assertNotNull($notification);
        $this->assertSame('three_days_after', $notification->data['timing']);
    }

    public function test_command_does_not_notify_completed_plan(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => today()->toDateString(),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        $this->artisan('reading-plans:check')
            ->assertSuccessful();

        $this->assertCount(0, $user->notifications);
    }

    public function test_command_does_not_send_duplicate_notification_for_same_timing(): void
    {
        $this->seed();

        $user = User::first();
        $book = Book::first();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => today()->addDays(3)->toDateString(),
            'status' => ReadingPlanStatus::Planned,
        ]);

        $this->artisan('reading-plans:check')->assertSuccessful();
        $this->artisan('reading-plans:check')->assertSuccessful();

        $notifications = $user->notifications()
            ->where('data->reading_plan_id', $readingPlan->id)
            ->where('data->timing', 'three_days_before')
            ->get();

        $this->assertCount(1, $notifications);
    }
}
