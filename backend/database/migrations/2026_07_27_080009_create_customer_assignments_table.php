<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('pending');   // pending|contacted|recovered|expired
            $table->timestamp('assigned_at');
            $table->timestamp('recovered_at')->nullable();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            // Drives the "find an open assignment" lookup in CreditRecoveryKpi.
            $table->index(['customer_id', 'status']);
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_assignments');
    }
};
