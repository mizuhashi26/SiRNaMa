<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('total_targets')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_reminder_id')->constrained('payment_reminders')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('recipient_name');
            $table->string('whatsapp_number', 30);
            $table->string('status', 30)->index();
            $table->text('provider_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reminder_logs');
        Schema::dropIfExists('payment_reminders');
    }
};

