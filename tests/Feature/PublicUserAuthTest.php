<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicUserAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/public/auth/register', [
            'name' => 'Sarah Aulia',
            'email' => 'sarah@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $user = User::where('email', 'sarah@test.com')->first();
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole('user');

        $response = $this->postJson('/api/v1/public/auth/login', [
            'email' => 'user@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_admin_cannot_login_via_public_auth(): void
    {
        $this->createAdminUser(['email' => 'admin@test.com', 'password' => bcrypt('password123')]);

        $response = $this->postJson('/api/v1/public/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_view_and_update_profile(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        $user->assignRole('user');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/public/auth/profile')
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Old Name');

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/public/auth/profile', ['name' => 'New Name'])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $token = $user->createToken('user-token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/public/auth/logout')
            ->assertStatus(200);
    }
}
