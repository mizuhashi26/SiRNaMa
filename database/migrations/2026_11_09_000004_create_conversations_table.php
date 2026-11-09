<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20)->index(); // web, whatsapp
            $table->string('external_user_id', 100)->index();
            $table->string('user_name')->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('status', 30)->default('BOT_ACTIVE')->index(); // BOT_ACTIVE, WAITING_ADMIN, ADMIN_ACTIVE, CLOSED
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('taken_over_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['channel', 'external_user_id']);
        });

        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('sender_type', 20); // user, bot, admin, system
            $table->string('sender_name')->nullable();
            $table->text('message');
            $table->string('delivery_status', 30)->default('delivered'); // delivered, sent_gateway, failed_gateway, local_only
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversations');
    }
};

