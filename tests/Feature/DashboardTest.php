<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard.index'))
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_displays_only_the_authenticated_users_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-OWN12345',
            'status' => 'shipped',
        ]);
        Order::factory()->create([
            'user_id' => $otherUser->id,
            'order_number' => 'ORD-OTHER123',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('shipped')
            ->assertDontSee('ORD-OTHER123');
    }

    public function test_user_can_save_their_theme_preference(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('dashboard.theme.update'), ['theme' => 'dark'])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'theme' => 'dark',
        ]);

        $this->actingAs($user->fresh())
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('data-theme="dark"', false);
    }

    public function test_user_can_change_password_after_confirming_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);

        $this->actingAs($user)
            ->put(route('dashboard.password.update'), [
                'current_password' => 'current-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_password_cannot_be_changed_with_an_incorrect_current_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password']);

        $this->actingAs($user)
            ->from(route('dashboard.index'))
            ->put(route('dashboard.password.update'), [
                'current_password' => 'incorrect-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
    }
}
