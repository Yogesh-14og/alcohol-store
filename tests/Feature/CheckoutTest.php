<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    public function test_checkout_requires_authentication(): void
    {
        $this->get(route('checkout.index'))
            ->assertRedirect(route('login'));
    }

    public function test_checkout_redirects_to_cart_when_cart_is_empty(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'));
    }

    public function test_cash_on_delivery_checkout_creates_an_order_and_clears_the_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Reserve Bottle',
            'price' => 150.25,
            'cost_price' => 90.00,
            'stock_quantity' => 10,
        ]);
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)
            ->post(route('checkout.store'), [
                'name' => 'Customer Name',
                'phone' => '+977 9800000000',
                'delivery_address' => 'Kathmandu, Nepal',
                'payment_method' => 'cod',
            ]);

        $order = Order::query()->where('user_id', $user->id)->firstOrFail();
        $response->assertRedirect(route('orders.confirmation', $order->order_number));

        $this->assertSame('300.50', $order->total_amount);
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame('pending', $order->status);
        $this->assertSame('Customer Name', $order->shipping_name);
        $this->assertSame('+977 9800000000', $order->shipping_phone);
        $this->assertSame('Kathmandu, Nepal', $order->shipping_address);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Reserve Bottle',
            'unit_price' => '150.25',
            'unit_cost' => '90.00',
            'quantity' => 2,
        ]);
        $product->update(['cost_price' => 110.00]);
        $this->assertSame(12050, $order->gross_profit_cents);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'unit_cost' => '90.00',
        ]);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 10,
        ]);
    }

    public function test_checkout_rejects_quantity_above_available_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 3]);
        $existingOrder = Order::factory()->create(['status' => 'pending']);
        $existingOrder->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 2,
        ]);
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'name' => 'Customer Name',
                'phone' => '9800000000',
                'delivery_address' => 'Kathmandu, Nepal',
                'payment_method' => 'cod',
            ])
            ->assertSessionHasErrors('stock');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 3,
        ]);
    }

    public function test_order_owner_can_view_their_confirmation(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-CONFIRM123',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);
        $order->items()->create([
            'product_name' => 'Reserve Bottle',
            'unit_price' => '150.25',
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('orders.confirmation', $order->order_number))
            ->assertOk()
            ->assertSee('Order placed successfully')
            ->assertSee($order->order_number)
            ->assertSee('Reserve Bottle')
            ->assertSee('Cash on delivery')
            ->assertSee('300.50');
    }

    public function test_user_cannot_view_another_users_order_confirmation(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'order_number' => 'ORD-PRIVATE123',
        ]);

        $this->actingAs($user)
            ->get(route('orders.confirmation', $order->order_number))
            ->assertNotFound();
    }

    public function test_order_confirmation_requires_authentication(): void
    {
        $order = Order::factory()->create();

        $this->get(route('orders.confirmation', $order->order_number))
            ->assertRedirect(route('login'));
    }

    public function test_fonepay_cannot_be_selected_until_its_official_integration_is_configured(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'name' => 'Customer Name',
                'phone' => '9800000000',
                'delivery_address' => 'Kathmandu, Nepal',
                'payment_method' => 'fonepay',
            ])
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }
}
