<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orderitems', function (Blueprint $table) {
            $table->id('OrderItemId');
            $table->unsignedBigInteger('OrderId')->nullable();
            $table->unsignedBigInteger('ProductId')->nullable();
            $table->string('ProductName', 255)->nullable();
            $table->decimal('UnitPrice', 10, 2)->nullable();
            $table->integer('Quantity')->nullable();
            $table->decimal('TotalPrice', 10, 2)->nullable();
            $table->foreign('OrderId')->references('OrderId')->on('orders');
            $table->foreign('ProductId')->references('ProductID')->on('products');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orderitems');
    }
};
