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
        Schema::table('payment_reminders', function (Blueprint $table) {
            $table->string('target_type', 20)->default('all')->after('category');
            $table->json('target_student_ids')->nullable()->after('target_type');
            $table->text('custom_recipients')->nullable()->after('target_student_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_reminders', function (Blueprint $table) {
            $table->dropColumn(['target_type', 'target_student_ids', 'custom_recipients']);
        });
    }
};

