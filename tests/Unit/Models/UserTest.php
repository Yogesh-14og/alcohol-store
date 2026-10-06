<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->id);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
    }

    public function test_user_has_hashed_password(): void
    {
        $user = User::factory()->create(['password' => 'plain-text-password']);

        $this->assertNotEquals('plain-text-password', $user->password);
        $this->assertTrue($user->password !== 'plain-text-password');
    }

    public function test_user_can_have_orders(): void
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->orders);
        $this->assertEquals($user->id, $orders->first()->user_id);
    }

    public function test_user_fillable_attributes(): void
    {
        $fillable = (new User)->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    public function test_user_hidden_attributes(): void
    {
        $hidden = (new User)->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    public function test_user_casts(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
    }
}
