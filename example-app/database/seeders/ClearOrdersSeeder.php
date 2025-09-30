<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;

class ClearOrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Safely clear order-related tables only
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        OrderItem::truncate();
        Order::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Cleared orders and orderitems tables.');
    }
}

