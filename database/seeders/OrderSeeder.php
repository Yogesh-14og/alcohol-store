<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'khatriyogesh@gmail.com'],
            ['name' => 'John Doe', 'password' => bcrypt('12345678')]
        );

        Order::firstOrCreate(
            ['order_number' => 'ORD-1001'],
            [
                'user_id' => $user->id,
                'total_amount' => 5300.00,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'created_at' => now()->subDays(5),
            ]
        );

        Order::firstOrCreate(
            ['order_number' => 'ORD-1002'],
            [
                'user_id' => $user->id,
                'total_amount' => 1800.00,
                'status' => 'processing',
                'payment_status' => 'paid',
                'created_at' => now()->subDays(2),
            ]
        );

        Order::firstOrCreate(
            ['order_number' => 'ORD-1003'],
            [
                'user_id' => $user->id,
                'total_amount' => 3500.00,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'created_at' => now(),
            ]
        );
    }
}
