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
        Schema::create('machine_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('operator_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->timestamps();

            $table->unique(['machine_id', 'date']);
            $table->index(['date', 'operator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_assignments');
    }
};
