<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => true])
                 ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_admin_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Email atau password salah',
                 ]);
    }

    public function test_admin_cannot_login_with_non_existent_email(): void
    {
        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'ghost@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['status' => false]);
    }

    public function test_admin_registration_requires_auth(): void
    {
        $response = $this->postJson('/api/v1/admin/register', [
            'name' => 'New Admin',
            'email' => 'new@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['status' => false]);
    }

    public function test_authenticated_admin_can_register_new_admin(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/register', [
            'name' => 'New Admin',
            'email' => 'new@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['status' => true])
                 ->assertJsonPath('data.email', 'new@test.com');

        $this->assertDatabaseHas('users', ['email' => 'new@test.com']);

        $newUser = User::where('email', 'new@test.com')->first();
        $this->assertTrue($newUser->hasRole('admin'));
    }

    public function test_admin_cannot_register_with_existing_email(): void
    {
        $this->createAdminUser(['email' => 'existing@test.com']);

        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/register', [
            'name' => 'Duplicate',
            'email' => 'existing@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJson(['status' => false])
                 ->assertJsonStructure(['data' => ['errors' => ['email']]]);
    }

    public function test_admin_cannot_register_with_weak_password(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/register', [
            'name' => 'Short Pass',
            'email' => 'short@test.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['data' => ['errors' => ['password']]]);
    }

    public function test_user_without_admin_role_cannot_register_admin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/admin/register', [
            'name' => 'New Admin',
            'email' => 'new@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_profile_returns_correct_data(): void
    {
        $user = $this->createAdminUser(['name' => 'John Doe']);
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/profile');

        $response->assertStatus(200)
                 ->assertJson(['status' => true])
                 ->assertJsonPath('data.name', 'John Doe');
    }

    public function test_profile_fails_without_token(): void
    {
        $response = $this->getJson('/api/v1/admin/profile');
        $response->assertStatus(401)
                 ->assertJson(['status' => false]);
    }

    public function test_logout_deletes_current_token(): void
    {
        $user = $this->createAdminUser();
        $token = $user->createToken('admin-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/logout');

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        $this->assertCount(0, DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->get());
    }

    public function test_forgot_password_sends_link(): void
    {
        User::factory()->create(['email' => 'resetme@test.com']);

        $response = $this->postJson('/api/v1/admin/forgot-password', [
            'email' => 'resetme@test.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => true, 'message' => 'We have emailed your password reset link.']);

        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'resetme@test.com']);
    }

    public function test_forgot_password_fails_if_email_not_found(): void
    {
        $response = $this->postJson('/api/v1/admin/forgot-password', [
            'email' => 'notfound@test.com',
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'status' => false,
                     'message' => 'We can\'t find a user with that email address.',
                 ]);
    }

    public function test_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'resetme@test.com', 'password' => bcrypt('oldpassword')]);

        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/admin/reset-password', [
            'email' => 'resetme@test.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => $token,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => true, 'message' => 'Your password has been reset.']);
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        User::factory()->create(['email' => 'resetme@test.com', 'password' => bcrypt('oldpassword')]);

        $response = $this->postJson('/api/v1/admin/reset-password', [
            'email' => 'resetme@test.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'invalid-random-token',
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'status' => false,
                     'message' => 'This password reset token is invalid.',
                 ]);
    }
}
