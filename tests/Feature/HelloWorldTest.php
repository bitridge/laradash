<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelloWorldTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_homepage_returns_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test if we can see Hello World text.
     *
     * @return void
     */
    public function test_homepage_contains_hello_world()
    {
        $response = $this->get('/');

        $response->assertSee('Laravel');
    }
} 