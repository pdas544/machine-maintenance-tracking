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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained();
            $table->foreignId('raised_by')->constrained('users');
            $table->foreignId('assigned_mechanic_id')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'unfixable_escalated'])->default('pending');
            $table->text('issue_description');
            $table->text('mechanic_remarks')->nullable();
            $table->timestamp('raised_at')->useCurrent();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
