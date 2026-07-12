<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_index_returns_successful_response(): void
    {
        $this->seed();

        $response = $this->get('/books');

        $response->assertStatus(200);
    }
}
