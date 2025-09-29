<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create();
    }

    public function test_toggle_user_status()
    {
        $this->actingAs($this->admin);
        $initialStatus = $this->user->is_active;

        $response = $this->put(route('admin.users.toggle-status', $this->user));

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success');
        $this->assertNotEquals($initialStatus, $this->user->fresh()->is_active);
    }

    public function test_non_admin_cannot_access_user_management()
    {
        $regularUser = User::factory()->create(['is_admin' => false]);
        $this->actingAs($regularUser);

        $response = $this->get(route('admin.users'));
        $response->assertStatus(403);

        $response = $this->put(route('admin.users.toggle-status', $this->user));
        $response->assertStatus(403);

        $response = $this->delete(route('admin.users.delete', $this->user));
        $response->assertStatus(403);
    }

    public function test_cannot_delete_active_user()
    {
        $this->actingAs($this->admin);
        $activeUser = User::factory()->create(['is_active' => true]);

        $response = $this->delete(route('admin.users.delete', $activeUser));

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $activeUser->id]);
    }
}