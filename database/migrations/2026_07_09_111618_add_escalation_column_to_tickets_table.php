<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // 0: None, 1: Floor Incharge (30m), 2: Head (1h), 3: Manager (1d), 4: Escalated (2d)
            $table->integer('escalation_level')->default(0)->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('escalation_level');
        });
    }
};
