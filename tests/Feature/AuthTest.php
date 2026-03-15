<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'root']);
    }

    public function test_signup_creates_user_and_returns_201(): void
    {
        $response = $this->postJson('/api/signup', [
            'name' => 'Test User',
            'cpf' => '123.456.789-00',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => __('User registered successfully.'),
                'user' => [
                    'name' => 'Test User',
                    'cpf' => '123.456.789-00',
                    'email' => 'test@example.com',
                ],
            ]);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_signup_fails_with_invalid_data(): void
    {
        $response = $this->postJson('/api/signup', [
            'name' => '',
            'cpf' => '123',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['name', 'token'])
            ->assertJson(['name' => $user->name]);
    }

    public function test_login_returns_401_for_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'exists@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'exists@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => __('Invalid credentials.')]);
    }

    public function test_me_returns_401_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_me_returns_user_when_authenticated(): void
    {
        $user = User::factory()->create(['name' => 'Logged User']);
        $user->role; // ensure role is loaded

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => 'Logged User',
                'email' => $user->email,
            ]);
    }

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => __('Successfully signed off.')]);
    }

    public function test_delete_own_account_succeeds_for_regular_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/user');

        $response->assertStatus(200)
            ->assertJson(['message' => __('Account deleted successfully.')]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_own_account_denied_for_root_user(): void
    {
        $rootRole = Role::firstOrCreate(['name' => 'root']);
        $root = User::factory()->create(['id_role' => $rootRole->id]);

        $response = $this->actingAs($root, 'sanctum')->deleteJson('/api/user');

        $response->assertStatus(403)
            ->assertJson(['message' => __('You cannot delete the root user.')]);
        $this->assertDatabaseHas('users', ['id' => $root->id]);
    }
}
