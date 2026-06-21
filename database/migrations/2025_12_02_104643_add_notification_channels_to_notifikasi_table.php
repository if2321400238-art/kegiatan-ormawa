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
        Schema::table('notifikasi', function (Blueprint $table) {
            // Telegram user ID for direct messaging
            $table->string('telegram_id')->nullable()->after('user_id');

            // JSON field: store which channels were used & their status
            // Example: { "telegram": "sent", "email": "sent", "in_app": "sent" }
            $table->json('delivery_channels')->nullable()->after('pesan');

            // Delivery status: pending, sent, failed, delivered
            $table->string('delivery_status')->default('pending')->after('delivery_channels');

            // Track when notification was read (separate from dibaca_pada for compatibility)
            $table->timestamp('read_at')->nullable()->after('dibaca_pada');

            // Index untuk query cepat unread notifications
            $table->index('dibaca');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_id',
                'delivery_channels',
                'delivery_status',
                'read_at',
            ]);
        });
    }
};
