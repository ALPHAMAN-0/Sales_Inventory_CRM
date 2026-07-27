<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->integer('kpi_score')->default(0);   // cache of SUM(kpi_events.points)
            $table->timestamps();

            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
