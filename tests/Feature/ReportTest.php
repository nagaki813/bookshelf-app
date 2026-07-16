<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_report_page(): void
    {
        $this->seed();

        $user = User::first();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('マイ読書レポート');
        $response->assertSee('総レビュー数');
        $response->assertSee('読了冊数');
        $response->assertSee('平均評価');
        $response->assertSee('評価分布');
        $response->assertSee('高評価書籍 TOP5');
        $response->assertSee('ジャンル別評価傾向 TOP5');
    }

    public function test_guest_cannot_view_report_page(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }
}
