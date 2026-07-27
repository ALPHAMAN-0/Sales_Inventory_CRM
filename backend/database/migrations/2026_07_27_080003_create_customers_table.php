<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('status')->default('active');   // active|lost|recovered
            $table->timestamp('first_purchase_at')->nullable();
            $table->timestamp('last_purchase_at')->nullable();
            $table->unsignedInteger('total_orders')->default(0);   // denormalized aggregate
            $table->decimal('total_spent', 14, 2)->default(0);     // denormalized aggregate
            $table->timestamps();

            // Drives the lost-customer detection scan.
            $table->index(['status', 'last_purchase_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
