<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_notifications_index(): void
    {
        $this->seed();

        $user = User::first();

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'reading_plan_reminder',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => '読書計画の期日が近づいています',
                'body' => '対象書籍の読書計画を確認してください。',
                'timing' => 'three_days_before',
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('通知一覧');
        $response->assertSee('読書計画の期日が近づいています');
        $response->assertSee('対象書籍の読書計画を確認してください。');
    }

    public function test_guest_cannot_view_notifications_index(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_mark_notification_as_read(): void
    {
        $this->seed();

        $user = User::first();

        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'reading_plan_reminder',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => '読書計画の期日です',
                'body' => '読書計画の期日になりました。',
                'timing' => 'on_due_date',
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read', $notificationId));

        $response->assertRedirect(route('notifications.index'));

        $notification = DB::table('notifications')
            ->where('id', $notificationId)
            ->first();

        $this->assertNotNull($notification->read_at);
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $this->seed();

        $user = User::first();
        $otherUser = User::where('id', '!=', $user->id)->first();

        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'reading_plan_reminder',
            'notifiable_type' => User::class,
            'notifiable_id' => $otherUser->id,
            'data' => json_encode([
                'title' => '他人の通知',
                'body' => '他人の通知本文です。',
                'timing' => 'on_due_date',
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read', $notificationId));

        $response->assertStatus(404);

        $notification = DB::table('notifications')
            ->where('id', $notificationId)
            ->first();

        $this->assertNull($notification->read_at);
    }
}
