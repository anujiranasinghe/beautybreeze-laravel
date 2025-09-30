<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetAllProductsStockToTwentySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->update(['StockQuantity' => 20]);
        $this->command->info('SetAllProductsStockToTwentySeeder: set StockQuantity=20 for all products.');
    }
}

