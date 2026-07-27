<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only ledger. employees.kpi_score is a cache of SUM(points) here.
        Schema::create('kpi_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('customer_assignments')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('points');
            $table->string('reason');
            $table->timestamps();

            // One KPI award per assignment — a hard DB backstop for idempotency
            // (nullable: MySQL permits multiple NULLs, for future non-assignment reasons).
            $table->unique('assignment_id');
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_events');
    }
};
