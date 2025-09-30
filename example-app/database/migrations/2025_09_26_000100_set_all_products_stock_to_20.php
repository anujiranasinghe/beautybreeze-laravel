<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->update(['StockQuantity' => 20]);
    }

    public function down(): void
    {
        // Revert to NULL to avoid unintended data loss
        DB::table('products')->update(['StockQuantity' => null]);
    }
};

