<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class TermsPageTest extends TestCase
{
    public function test_terms_page_is_available_to_visitors(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('Terms &amp; Conditions')
            ->assertSee('Legal eligibility')
            ->assertSee('Cash on delivery');
    }

    public function test_checkout_links_to_the_terms_page(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 4]);
        CartItem::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee(route('terms'))
            ->assertSee('Terms &amp; Conditions');
    }
}
