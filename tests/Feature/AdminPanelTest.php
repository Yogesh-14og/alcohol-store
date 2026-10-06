<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    public function test_admin_panel_redirects_guests_to_the_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_customer_login_does_not_grant_admin_access(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_sign_in_and_open_the_dashboard(): void
    {
        $admin = Admin::create([
            'name' => 'Store Admin',
            'email' => 'admin@example.com',
            'password' => 'Secure-Admin-Password-123!',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'Secure-Admin-Password-123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin dashboard')
            ->assertSee('data-bs-toggle="offcanvas"', false)
            ->assertSee('offcanvas-start', false)
            ->assertSee('Admin menu')
            ->assertSee('adminProfileMenu', false)
            ->assertSee('data-admin-theme-toggle', false)
            ->assertSee('Dark theme')
            ->assertSee('Sign out');
    }

    public function test_admin_can_search_products_by_name_slug_volume_or_category(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create(['name' => 'Needle Category']);
        Product::factory()->create([
            'name' => 'Matching Product',
            'slug' => 'needle-product',
            'category_id' => $category->id,
        ]);
        Product::factory()->create(['name' => 'Different Product']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index', ['search' => 'needle']))
            ->assertOk()
            ->assertSee('Matching Product')
            ->assertDontSee('Different Product')
            ->assertSee('value="needle"', false)
            ->assertSee('Search products')
            ->assertSee('data-live-search', false);
    }

    public function test_admin_can_search_orders_by_order_number_and_customer_details(): void
    {
        $admin = $this->createAdmin();
        $customer = User::factory()->create([
            'name' => 'Needle Customer',
            'email' => 'needle.customer@example.com',
        ]);
        Order::factory()->create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-NEEDLE-123',
        ]);
        Order::factory()->create(['order_number' => 'ORD-OTHER-456']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index', ['search' => 'needle']))
            ->assertOk()
            ->assertSee('ORD-NEEDLE-123')
            ->assertDontSee('ORD-OTHER-456')
            ->assertSee('Needle Customer')
            ->assertSee('value="needle"', false)
            ->assertSee('Search orders')
            ->assertSee('data-live-search', false);
    }

    public function test_admin_can_create_a_product(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), [
                'name' => 'Admin Created Drink',
                'slug' => 'admin-created-drink',
                'category_id' => $category->id,
                'price' => '399.50',
                'cost_price' => '250.00',
                'stock_quantity' => 12,
                'volume' => '750ml',
                'abv' => '40%',
                'is_best_seller' => '1',
            ]);

        $response->assertRedirect(route('admin.products.index'));

        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Product created.')
            ->assertSee('id="adminStatusToast"', false)
            ->assertSee('position-fixed top-0 end-0', false);

        $this->assertDatabaseHas('products', [
            'name' => 'Admin Created Drink',
            'slug' => 'admin-created-drink',
            'category_id' => $category->id,
            'cost_price' => '250.00',
            'stock_quantity' => 12,
            'is_best_seller' => true,
            'is_new_arrival' => false,
        ]);
    }

    public function test_admin_can_create_a_product_without_a_redirect_for_the_form_popup(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.products.store'), [
                'name' => 'No Refresh Drink',
                'price' => '400.00',
                'cost_price' => '250.00',
                'stock_quantity' => 8,
            ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Product created.')
            ->assertJsonStructure(['product' => ['id']]);

        $this->assertDatabaseHas('products', [
            'name' => 'No Refresh Drink',
            'slug' => 'no-refresh-drink',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('data-product-create', false)
            ->assertDontSee('data-ajax-product-create', false)
            ->assertSee('event.preventDefault(); event.stopImmediatePropagation(); submitProductCreate(this); return false;', false);
    }

    public function test_product_list_shows_gross_margin_percentage_and_amount(): void
    {
        $admin = $this->createAdmin();
        Product::factory()->create([
            'name' => 'Margin Bottle',
            'price' => '400.00',
            'cost_price' => '250.00',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('37.5%')
            ->assertSee('NPR 150.00 / unit');
    }

    public function test_purchase_increases_supplier_balance_and_stock_and_supplier_payments_reduce_balance(): void
    {
        $admin = $this->createAdmin();
        $supplier = Supplier::create(['name' => 'Local Distributor']);
        $product = Product::factory()->create([
            'stock_quantity' => 3,
            'cost_price' => '80.00',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.purchase-ledger.purchases.store'), [
                'supplier_id' => $supplier->id,
                'purchased_at' => '2026-10-06',
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 4,
                    'unit_cost' => '95.00',
                ]],
            ])
            ->assertRedirect(route('admin.purchase-ledger.suppliers.show', $supplier));

        $purchase = Purchase::query()->firstOrFail();
        $this->assertSame('380.00', $purchase->total_amount);
        $this->assertSame(sprintf('PUR-20261006-%06d', $purchase->id), $purchase->invoice_number);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 7,
            'cost_price' => '95.00',
        ]);

        $this->post(route('admin.purchase-ledger.payments.store', [$supplier, $purchase]), [
            'amount' => '125.00',
            'method' => 'cash',
        ])->assertRedirect(route('admin.purchase-ledger.suppliers.show', $supplier));

        $this->get(route('admin.purchase-ledger.suppliers.show', $supplier))
            ->assertOk()
            ->assertSee('NPR 255.00');

        $this->from(route('admin.purchase-ledger.suppliers.show', $supplier))
            ->post(route('admin.purchase-ledger.payments.store', [$supplier, $purchase]), [
                'amount' => '255.01',
                'method' => 'cash',
            ])
            ->assertSessionHasErrors('amount');

        $this->post(route('admin.purchase-ledger.payments.store', [$supplier, $purchase]), [
            'amount' => '255.00',
            'method' => 'bank_transfer',
        ])->assertRedirect(route('admin.purchase-ledger.suppliers.show', $supplier));

        $this->assertSame(0, $purchase->fresh()->balance_due_cents);
    }

    public function test_suppliers_have_a_dedicated_admin_page_for_listing_and_creation(): void
    {
        $admin = $this->createAdmin();
        Supplier::create([
            'name' => 'Dedicated Page Supplier',
            'email' => 'supplier@example.com',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.suppliers.index'))
            ->assertOk()
            ->assertSee('Dedicated Page Supplier')
            ->assertSee('Add supplier')
            ->assertSee(route('admin.suppliers.create'), false);

        $this->get(route('admin.suppliers.create'))
            ->assertOk()
            ->assertSee(route('admin.suppliers.store'), false)
            ->assertSee('← Suppliers');

        $this->post(route('admin.suppliers.store'), [
            'name' => 'New Page Supplier',
            'phone' => '9800000000',
        ])->assertRedirect(route('admin.suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'name' => 'New Page Supplier',
            'phone' => '9800000000',
        ]);

        $this->get(route('admin.purchase-ledger.index'))
            ->assertOk()
            ->assertDontSee('Add supplier');
    }

    public function test_delivered_sale_is_automatically_paid_and_appears_in_sales_ledger(): void
    {
        $admin = $this->createAdmin();
        $customer = User::factory()->create(['name' => 'Khata Customer']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'delivered',
            'total_amount' => '100.00',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.sales-ledger.index'))
            ->assertOk()
            ->assertSee('Khata Customer')
            ->assertSee('NPR 100.00')
            ->assertSee('Collected on delivery')
            ->assertSee('Choose sales history columns')
            ->assertSee('data-sales-column-toggle', false)
            ->assertSee('data-sales-history-column="payments"', false);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'unpaid',
        ]);
        $this->assertSame(0, $order->fresh()->balance_due_cents);
        $this->assertSame('paid', $order->fresh()->effective_payment_status);
        $this->assertDatabaseCount('customer_payments', 0);

        $this->from(route('admin.sales-ledger.index'))
            ->post(route('admin.sales-ledger.payments.store', $order), [
                'amount' => '0.01',
                'method' => 'cash',
            ])
            ->assertSessionHasErrors('amount');
    }

    public function test_all_delivered_orders_are_counted_as_received_without_manual_payment_entry(): void
    {
        $admin = $this->createAdmin();
        $customer = User::factory()->create(['name' => 'Legacy Paid Customer']);
        Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'delivered',
            'total_amount' => '125.00',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.sales-ledger.index'))
            ->assertOk()
            ->assertSee('An order marked delivered is counted as sold and paid automatically.')
            ->assertSee('NPR 125.00')
            ->assertSee('Collected on delivery')
            ->assertDontSee('Reconcile');

        $this->assertDatabaseCount('customer_payments', 0);
    }

    public function test_marking_an_order_delivered_also_marks_payment_as_paid(): void
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create([
            'status' => 'shipped',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $order), ['status' => 'delivered'])
            ->assertRedirect(route('admin.orders.invoice', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
        ]);
    }

    public function test_admin_can_update_order_status_without_manually_changing_ledger_payment_status(): void
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create(['payment_status' => 'unpaid']);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status' => 'shipped',
            ])
            ->assertRedirect(route('admin.orders.invoice', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_admin_saving_a_cancelled_order_returns_to_order_details_without_printing_invoice(): void
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create(['status' => 'pending']);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $order), ['status' => 'cancelled'])
            ->assertRedirect(route('admin.orders.show', $order));
    }

    public function test_stock_is_deducted_once_when_order_is_delivered_and_restored_if_reopened(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $order = Order::factory()->create(['status' => 'shipped']);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 3,
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $order), [
                'status' => 'delivered',
            ])
            ->assertRedirect(route('admin.orders.invoice', $order));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 2]);
        $deliveredAt = $order->fresh()->delivered_at;
        $this->assertNotNull($deliveredAt);

        $this->put(route('admin.orders.update', $order), [
            'status' => 'delivered',
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 2]);
        $this->assertTrue($deliveredAt->equalTo($order->fresh()->delivered_at));

        $this->put(route('admin.orders.update', $order), [
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 5]);
        $this->assertNull($order->fresh()->delivered_at);
    }

    public function test_delivered_order_with_recorded_customer_payments_cannot_be_reopened(): void
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create(['status' => 'delivered']);
        $order->customerPayments()->create([
            'amount' => '10.00',
            'method' => 'cash',
            'received_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.orders.show', $order))
            ->put(route('admin.orders.update', $order), ['status' => 'cancelled'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'delivered',
        ]);
    }

    public function test_admin_cannot_adjust_stock_below_open_order_quantities(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $order = Order::factory()->create(['status' => 'pending']);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 4,
        ]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.products.stock-adjustments.index', $product))
            ->post(route('admin.products.stock-adjustments.store', $product), [
                'direction' => 'remove',
                'quantity' => 3,
                'reason' => 'stock_count',
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 5,
        ]);
    }

    public function test_admin_can_restock_product_without_creating_a_supplier_purchase_and_history_is_recorded(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create(['stock_quantity' => 4]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.stock-adjustments.index', $product))
            ->assertOk()
            ->assertSee('Adjust stock')
            ->assertSee('does not create a supplier purchase or supplier balance')
            ->assertSee(route('admin.products.stock-adjustments.store', $product), false);

        $this->post(route('admin.products.stock-adjustments.store', $product), [
            'direction' => 'add',
            'quantity' => 6,
            'reason' => 'restock',
            'notes' => 'Shelf restock',
        ])->assertRedirect(route('admin.products.stock-adjustments.index', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 10,
        ]);
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $product->id,
            'admin_id' => $admin->id,
            'direction' => 'add',
            'quantity' => 6,
            'stock_before' => 4,
            'stock_after' => 10,
            'reason' => 'restock',
            'notes' => 'Shelf restock',
        ]);
        $this->assertDatabaseCount('purchases', 0);

        $this->get(route('admin.products.stock-adjustments.index', $product))
            ->assertOk()
            ->assertSee('+6')
            ->assertSee('Restock')
            ->assertSee('Shelf restock')
            ->assertSee($admin->name);
    }

    public function test_existing_product_stock_cannot_be_changed_from_the_product_edit_form(): void
    {
        $admin = $this->createAdmin();
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Adjust stock / view history')
            ->assertDontSee('name="stock_quantity"', false);

        $this->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), [
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'cost_price' => $product->cost_price,
                'stock_quantity' => 1,
            ])
            ->assertSessionHasErrors('stock_quantity');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 5,
        ]);
    }

    public function test_admin_can_print_invoice_for_processing_and_later_orders(): void
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create([
            'status' => 'processing',
            'payment_method' => 'cod',
            'shipping_name' => 'Invoice Customer',
        ]);
        $order->items()->create([
            'product_name' => 'Invoice Bottle',
            'unit_price' => '100.00',
            'quantity' => 2,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.invoice', $order))
            ->assertOk()
            ->assertSee('Invoice Bottle')
            ->assertSee('Invoice Customer')
            ->assertSee('200.00')
            ->assertSee('window.print()')
            ->assertSee('afterprint')
            ->assertSee(route('admin.orders.show', $order));
    }

    public function test_dashboard_reports_gross_profit_for_delivered_orders_and_excludes_missing_costs(): void
    {
        $admin = $this->createAdmin();
        $completeOrder = Order::factory()->create([
            'status' => 'delivered',
            'total_amount' => '300.00',
        ]);
        $completeOrder->items()->create([
            'product_name' => 'Known Cost Bottle',
            'unit_price' => '150.00',
            'unit_cost' => '90.00',
            'quantity' => 2,
        ]);
        $legacyOrder = Order::factory()->create(['status' => 'delivered']);
        $legacyOrder->items()->create([
            'product_name' => 'Unknown Cost Bottle',
            'unit_price' => '100.00',
            'quantity' => 1,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('NPR 120.00')
            ->assertSee('Gross profit excludes 1 delivered')
            ->assertSee('with missing product cost records.')
            ->assertSee('no delivery date yet');
    }

    public function test_dashboard_reports_sales_and_gross_profit_for_delivery_date_periods(): void
    {
        $this->travelTo(Carbon::parse('2026-10-06 12:00:00'));
        $admin = $this->createAdmin();

        foreach ([
            ['2026-10-06 10:00:00', '200.00', '100.00'],
            ['2026-10-05 10:00:00', '100.00', '60.00'],
            ['2026-09-30 10:00:00', '50.00', '30.00'],
        ] as [$deliveredAt, $price, $cost]) {
            $order = Order::factory()->create([
                'status' => 'delivered',
                'delivered_at' => $deliveredAt,
                'total_amount' => $price,
            ]);
            $order->items()->create([
                'product_name' => 'Report Bottle',
                'unit_price' => $price,
                'unit_cost' => $cost,
                'quantity' => 1,
            ]);
        }

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Today')
            ->assertSee('Yesterday')
            ->assertSee('Last 7 days')
            ->assertSee('This month')
            ->assertSee('Previous month')
            ->assertSee('Tue, Oct 6')
            ->assertSee('Mon, Oct 5')
            ->assertSee('NPR 200.00')
            ->assertSee('NPR 40.00')
            ->assertSee('NPR 50.00');
    }

    public function test_dashboard_stock_alerts_use_configurable_available_stock_thresholds(): void
    {
        $admin = $this->createAdmin();
        $lowStockProduct = Product::factory()->create([
            'name' => 'Low Stock Bottle',
            'stock_quantity' => 5,
        ]);
        $lowStockOrder = Order::factory()->create(['status' => 'pending']);
        $lowStockOrder->items()->create([
            'product_id' => $lowStockProduct->id,
            'product_name' => $lowStockProduct->name,
            'unit_price' => $lowStockProduct->price,
            'quantity' => 3,
        ]);
        $highStockProduct = Product::factory()->create([
            'name' => 'High Stock Bottle',
            'stock_quantity' => 30,
        ]);
        $highStockOrder = Order::factory()->create(['status' => 'processing']);
        $highStockOrder->items()->create([
            'product_id' => $highStockProduct->id,
            'product_name' => $highStockProduct->name,
            'unit_price' => $highStockProduct->price,
            'quantity' => 5,
        ]);
        Product::factory()->create([
            'name' => 'Normal Stock Bottle',
            'stock_quantity' => 12,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard', [
                'low_stock_threshold' => 2,
                'high_stock_threshold' => 20,
            ]))
            ->assertOk()
            ->assertSee('Low Stock Bottle')
            ->assertSee('High Stock Bottle')
            ->assertDontSee('Normal Stock Bottle')
            ->assertSee('value="2"', false)
            ->assertSee('value="20"', false);
    }

    public function test_invoice_is_not_available_until_order_is_processing(): void
    {
        $admin = $this->createAdmin();
        $order = Order::factory()->create(['status' => 'pending']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.invoice', $order))
            ->assertNotFound();
    }

    public function test_admin_can_update_customer_details_without_deleting_order_history(): void
    {
        $admin = $this->createAdmin();
        $customer = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.users.update', $customer), [
                'name' => 'Updated Customer',
                'email' => 'updated@example.com',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
        ]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => $customer->id]);
    }

    public function test_admin_password_is_hashed(): void
    {
        $admin = $this->createAdmin();

        $this->assertTrue(Hash::check('Secure-Admin-Password-123!', $admin->password));
    }

    private function createAdmin(): Admin
    {
        return Admin::create([
            'name' => 'Store Admin',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'Secure-Admin-Password-123!',
        ]);
    }
}
