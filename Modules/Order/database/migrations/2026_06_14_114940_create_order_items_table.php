<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // A plain reference to a Catalog product id — deliberately NOT a
            // foreign key. The Order module owns no Catalog tables, so coupling
            // its schema to the products table would break module DB boundaries.
            $table->unsignedBigInteger('product_id');

            // Snapshot of the product at purchase time, so the order stays
            // historically accurate even if the product later changes or is removed.
            $table->string('product_name');
            $table->unsignedBigInteger('unit_price')->comment('Snapshot unit price in cents; cast to Money');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total')->comment('quantity * unit_price in cents; cast to Money');

            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
