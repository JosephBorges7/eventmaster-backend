<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'organizer']);
        Role::firstOrCreate(['name' => 'root']);
        $this->admin = User::factory()->create([
            'id_role' => $adminRole->id,
            'email' => 'admin@test.com',
        ]);
    }

    public function test_guest_cannot_list_users(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_list_users(): void
    {
        $userRole = Role::where('name', 'user')->first();
        $user = User::factory()->create(['id_role' => $userRole->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(2)->create();

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/users');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_admin_can_create_user_with_role(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/users', [
            'name' => 'New User',
            'cpf' => '987.654.321-00',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'organizer',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => __('User created successfully.'),
                'user' => [
                    'name' => 'New User',
                    'email' => 'newuser@test.com',
                    'role' => 'organizer',
                ],
            ]);
        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
    }

    public function test_admin_can_update_user_role(): void
    {
        $target = User::factory()->create();
        $target->load('role');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/users/{$target->id}/role", ['role' => 'organizer']);

        $response->assertStatus(200)
            ->assertJson([
                'message' => __('User role updated successfully.'),
                'user' => ['role' => 'organizer'],
            ]);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')->deleteJson("/api/users/{$target->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => __('User deleted successfully.')]);
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_cannot_delete_themselves_via_users_endpoint(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/users/{$this->admin->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => __('You cannot delete yourself from the user management panel.')]);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_delete_nonexistent_user_returns_404_with_message(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->deleteJson('/api/users/99999');

        $response->assertStatus(404)
            ->assertJson(['message' => __('User not found.')]);
    }
}
