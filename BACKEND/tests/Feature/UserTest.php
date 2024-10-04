<?php
namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    // Comment or remove the use of RefreshDatabase
    // use RefreshDatabase;

    public function test_can_create_user()
    {
        // Create a user instance
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Assert that the user was created successfully
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
}
