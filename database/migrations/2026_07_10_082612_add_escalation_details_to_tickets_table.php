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
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('escalated_at')->nullable()->after('escalation_level');
            $table->foreignId('escalated_from_user_id')->nullable()->constrained('users')->after('escalated_at');
            $table->string('escalation_reason')->nullable()->after('escalated_from_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['escalated_from_user_id']);
            $table->dropColumn(['escalated_at', 'escalated_from_user_id', 'escalation_reason']);
        });
    }
};
