<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Command;

class CheckReadingPlans extends Command
{
    protected $signature = 'reading-plans:check';

    protected $description = 'Check reading plans and send reminder notifications.';

    public function handle(): int
    {
        $today = today();

        ReadingPlan::where('status', ReadingPlanStatus::Planned)
            ->whereDate('due_date', '<', $today)
            ->update([
                'status' => ReadingPlanStatus::Expired,
            ]);

        $this->sendReminders($today->copy()->addDays(3), 'three_days_before');
        $this->sendReminders($today, 'on_due_date');
        $this->sendReminders($today->copy()->subDays(3), 'three_days_after');

        $this->info('Reading plans checked successfully.');

        return self::SUCCESS;
    }

    private function sendReminders($targetDate, string $timing): void
    {
        ReadingPlan::with(['user', 'book'])
            ->whereIn('status', [
                ReadingPlanStatus::Planned,
                ReadingPlanStatus::Expired,
            ])
            ->whereDate('due_date', $targetDate)
            ->chunkById(100, function ($readingPlans) use ($timing) {
                foreach ($readingPlans as $readingPlan) {
                    if ($this->alreadyNotified($readingPlan, $timing)) {
                        continue;
                    }

                    $readingPlan->user->notify(
                        new ReadingPlanReminderNotification($readingPlan, $timing)
                    );

                    $readingPlan->update([
                        'reminder_sent_at' => now(),
                    ]);
                }
            });
    }

    private function alreadyNotified(ReadingPlan $readingPlan, string $timing): bool
    {
        return $readingPlan->user
            ->notifications()
            ->where('data->reading_plan_id', $readingPlan->id)
            ->where('data->timing', $timing)
            ->exists();
    }
}
