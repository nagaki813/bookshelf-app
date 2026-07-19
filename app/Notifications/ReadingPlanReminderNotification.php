<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ReadingPlan $readingPlan,
        private readonly string $timing
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
            'timing' => $this->timing,
            'reading_plan_id' => $this->readingPlan->id,
            'book_id' => $this->readingPlan->book_id,
            'book_title' => $this->readingPlan->book->title,
            'due_date' => $this->readingPlan->due_date->format('Y-m-d'),
        ];
    }

    private function title(): string
    {
        return match ($this->timing) {
            'three_days_before' => '読書計画の期日が近づいています',
            'on_due_date' => '読書計画の期日です',
            'three_days_after' => '読書計画の期日を過ぎています',
            default => '読書計画のお知らせ',
        };
    }

    private function body(): string
    {
        return match ($this->timing) {
            'three_days_before' => "「{$this->readingPlan->book->title}」の読書計画の期日まであと3日です。",
            'on_due_date' => "「{$this->readingPlan->book->title}」の読書計画の期日です。",
            'three_days_after' => "「{$this->readingPlan->book->title}」の読書計画の期日から3日経過しています。",
            default => "「{$this->readingPlan->book->title}」の読書計画を確認してください。",
        };
    }
}
