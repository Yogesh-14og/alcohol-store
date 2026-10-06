<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\User;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function test_order_can_be_created_with_factory(): void
    {
        $order = Order::factory()->create();

        $this->assertInstanceOf(Order::class, $order);
        $this->assertNotNull($order->id);
        $this->assertNotNull($order->user_id);
        $this->assertNotNull($order->order_number);
        $this->assertNotNull($order->total_amount);
        $this->assertNotNull($order->status);
        $this->assertNotNull($order->payment_status);
    }

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_fillable_attributes(): void
    {
        $fillable = (new Order)->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('order_number', $fillable);
        $this->assertContains('total_amount', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('payment_status', $fillable);
    }

    public function test_order_can_have_different_statuses(): void
    {
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        $completedOrder = Order::factory()->create(['status' => 'completed']);
        $cancelledOrder = Order::factory()->create(['status' => 'cancelled']);

        $this->assertEquals('pending', $pendingOrder->status);
        $this->assertEquals('completed', $completedOrder->status);
        $this->assertEquals('cancelled', $cancelledOrder->status);
    }

    public function test_order_can_have_different_payment_statuses(): void
    {
        $unpaidOrder = Order::factory()->create(['payment_status' => 'unpaid']);
        $paidOrder = Order::factory()->create(['payment_status' => 'paid']);
        $refundedOrder = Order::factory()->create(['payment_status' => 'refunded']);

        $this->assertEquals('unpaid', $unpaidOrder->payment_status);
        $this->assertEquals('paid', $paidOrder->payment_status);
        $this->assertEquals('refunded', $refundedOrder->payment_status);
    }
}
