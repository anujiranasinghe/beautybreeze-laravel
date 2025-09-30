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
        Schema::create('products', function (Blueprint $table) {
            $table->id('ProductID');
            $table->string('Title', 255);
            $table->decimal('Price', 10, 2);
            $table->text('Description')->nullable();
            $table->string('Image', 255)->nullable();
            $table->unsignedBigInteger('CategoryID')->nullable();
            $table->foreign('CategoryID')->references('CategoryID')->on('categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
