<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->token = $this->admin->createToken('auth_token')->plainTextToken;
    }

    /**
     * Test admin can view all users.
     */
    public function test_admin_can_view_all_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(4); // 3 + 1 admin
    }

    /**
     * Test admin can create a user.
     */
    public function test_admin_can_create_user(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'role' => 'guest',
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role', 'status']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role' => 'guest',
        ]);
    }

    /**
     * Test user creation fails with duplicate email.
     */
    public function test_user_creation_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'existing@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test admin can update a user.
     */
    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/users/{$user->id}", [
                'name' => 'Updated Name',
                'email' => $user->email,
                'status' => 'inactive',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'status' => 'inactive',
        ]);
    }

    /**
     * Test admin can delete a user.
     */
    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test unauthenticated user cannot view users.
     */
    public function test_unauthenticated_user_cannot_view_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }
}
