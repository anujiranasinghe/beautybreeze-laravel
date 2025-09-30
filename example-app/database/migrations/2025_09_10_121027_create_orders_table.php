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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('OrderId');
            $table->unsignedBigInteger('CustomerID')->nullable();
            $table->string('CustomerName', 255)->nullable();
            $table->string('PhoneNo', 20)->nullable();
            $table->text('Address')->nullable();
            $table->string('Email', 255)->nullable();
            $table->string('PaymentMethod', 50)->nullable();
            $table->string('PaymentStatus', 50)->nullable();
            $table->foreign('CustomerID')->references('CustomerID')->on('customers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
