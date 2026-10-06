<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone', 30)->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
                $table->string('invoice_number')->nullable();
                $table->date('purchased_at');
                $table->decimal('total_amount', 12, 2);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->string('product_name');
                $table->decimal('unit_cost', 10, 2);
                $table->unsignedInteger('quantity');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('supplier_payments')) {
            Schema::create('supplier_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained()->restrictOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('method', 40);
                $table->string('reference')->nullable();
                $table->timestamp('paid_at');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('customer_payments')) {
            Schema::create('customer_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->restrictOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('method', 40);
                $table->string('reference')->nullable();
                $table->timestamp('received_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
    }
};
