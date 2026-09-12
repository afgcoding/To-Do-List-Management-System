<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guests_are_redirected_to_the_login_screen(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
