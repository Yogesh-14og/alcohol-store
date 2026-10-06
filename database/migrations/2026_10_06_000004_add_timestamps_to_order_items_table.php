<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_items')) {
            return;
        }

        $missingTimestamps = [];

        foreach (['created_at', 'updated_at'] as $column) {
            if (! Schema::hasColumn('order_items', $column)) {
                $missingTimestamps[] = $column;
            }
        }

        if ($missingTimestamps !== []) {
            Schema::table('order_items', function (Blueprint $table) use ($missingTimestamps) {
                if (in_array('created_at', $missingTimestamps, true)) {
                    $table->timestamp('created_at')->nullable();
                }

                if (in_array('updated_at', $missingTimestamps, true)) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('order_items')
            && Schema::hasColumn('order_items', 'created_at')
            && Schema::hasColumn('order_items', 'updated_at')
        ) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropTimestamps();
            });
        }
    }
};
