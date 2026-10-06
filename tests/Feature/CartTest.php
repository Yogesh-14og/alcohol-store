<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class CartTest extends TestCase
{
    public function test_cart_requires_authentication(): void
    {
        $this->get(route('cart.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_add_a_product_to_their_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post(route('cart.items.store', $product))
            ->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_adding_the_same_product_increments_its_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->post(route('cart.items.store', $product))
            ->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_user_cannot_add_more_than_available_stock_to_their_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 1]);
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('cart.items.store', $product))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_homepage_stock_display_accounts_for_open_orders(): void
    {
        $product = Product::factory()->create([
            'name' => 'Limited Reserve',
            'stock_quantity' => 3,
            'is_best_seller' => true,
        ]);
        $order = Order::factory()->create(['status' => 'processing']);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 2,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('1 in stock')
            ->assertSee('Limited Reserve');
    }

    public function test_cart_shows_its_products_and_subtotal(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Local Reserve',
            'price' => 125.50,
            'stock_quantity' => 10,
        ]);
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Local Reserve')
            ->assertSee('251.00')
            ->assertSee('Proceed to checkout');
    }

    public function test_user_can_update_and_remove_their_cart_item(): void
    {
        $user = User::factory()->create();
        $item = CartItem::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('cart.items.update', $item), ['quantity' => 4])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 4,
        ]);

        $this->delete(route('cart.items.destroy', $item))
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_user_cannot_modify_another_users_cart_item(): void
    {
        $user = User::factory()->create();
        $otherUsersItem = CartItem::factory()->create();

        $this->actingAs($user)
            ->put(route('cart.items.update', $otherUsersItem), ['quantity' => 4])
            ->assertNotFound();

        $this->assertDatabaseHas('cart_items', [
            'id' => $otherUsersItem->id,
            'quantity' => $otherUsersItem->quantity,
        ]);
    }

    public function test_cart_quantity_must_be_between_one_and_ninety_nine(): void
    {
        $user = User::factory()->create();
        $item = CartItem::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->from(route('cart.index'))
            ->put(route('cart.items.update', $item), ['quantity' => 100])
            ->assertSessionHasErrors('quantity');
    }
}
