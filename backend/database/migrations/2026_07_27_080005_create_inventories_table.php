<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->integer('quantity')->default(0);
            $table->timestamps();

            // One stock row per product-at-location; this row is locked during a sale.
            $table->unique(['product_id', 'branch_id']);
        });

        // Final backstop: the DB itself refuses to go negative, even if
        // application code has a bug. (Schema builder can't express CHECK.)
        DB::statement('ALTER TABLE inventories ADD CONSTRAINT chk_inventory_qty CHECK (quantity >= 0)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE inventories DROP CONSTRAINT chk_inventory_qty');
        Schema::dropIfExists('inventories');
    }
};
