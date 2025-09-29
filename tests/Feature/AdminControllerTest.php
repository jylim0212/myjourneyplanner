<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_active' => true]);
    }

    public function test_index_displays_dashboard()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    }

    public function test_non_admin_cannot_access_dashboard()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_guests_cannot_access_dashboard()
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_user_statistics()
    {
        $this->actingAs($this->admin);
        User::factory()->count(5)->create();

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('users');
        $this->assertTrue($response->original->getData()['users']->count() > 0);
    }

    public function test_users_displays_user_list()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.users'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    }

    public function test_delete_user()
    {
        $this->actingAs($this->admin);
        $user = User::factory()->create(['is_active' => false]);

        $response = $this->delete(route('admin.users.delete', $user));

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_cannot_delete_own_account()
    {
        $this->actingAs($this->admin);

        $response = $this->delete(route('admin.users.delete', $this->admin));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_toggle_user_status()
    {
        $this->actingAs($this->admin);
        $user = User::factory()->create(['is_active' => true]);
        $initialStatus = $user->is_active;

        $response = $this->put(route('admin.users.toggle-status', $user));

        $response->assertRedirect(route('admin.users'));
        $response->assertSessionHas('success');
        $this->assertNotEquals($initialStatus, $user->fresh()->is_active);
    }

    public function test_cannot_toggle_own_status()
    {
        $this->actingAs($this->admin);
        $initialStatus = $this->admin->is_active;

        $response = $this->put(route('admin.users.toggle-status', $this->admin));

        $response->assertStatus(403);
        $this->assertEquals($initialStatus, $this->admin->fresh()->is_active);
    }
} 