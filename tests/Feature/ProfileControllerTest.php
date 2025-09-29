<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);
    }

    public function test_index_displays_profile_page()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('profile.index'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.index');
    }

    public function test_update_password_successfully()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);
        $this->actingAs($user);

        $response = $this->put(route('profile.update-password'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    public function test_update_password_fails_with_incorrect_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);
        $this->actingAs($user);

        $response = $this->put(route('profile.update-password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('oldpassword', $user->fresh()->password));
    }

    public function test_update_password_fails_with_unmatched_confirmation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);
        $this->actingAs($user);

        $response = $this->put(route('profile.update-password'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'differentpassword'
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('oldpassword', $user->fresh()->password));
    }

    public function test_update_password_requires_minimum_length()
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.update-password'), [
            'current_password' => 'oldpassword',
            'password' => 'short',
            'password_confirmation' => 'short'
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('oldpassword', $this->user->fresh()->password));
    }

    public function test_update_password_requires_all_fields()
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.update-password'), [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => ''
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHasErrors(['current_password', 'password']);
        $this->assertTrue(Hash::check('oldpassword', $this->user->fresh()->password));
    }

    public function test_guests_cannot_access_profile()
    {
        $response = $this->get(route('profile.index'));
        $response->assertRedirect(route('login'));

        $response = $this->put(route('profile.update-password'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);
        $response->assertRedirect(route('login'));
    }
} 