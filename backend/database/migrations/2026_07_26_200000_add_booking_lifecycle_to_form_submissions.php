<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->foreignId('provider_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->text('provider_note')->nullable()->after('status');
            $table->timestamp('accepted_at')->nullable()->after('provider_note');
            $table->timestamp('started_at')->nullable()->after('accepted_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_id');
            $table->dropColumn([
                'provider_note',
                'accepted_at',
                'started_at',
                'completed_at',
                'cancelled_at',
            ]);
        });
    }
};
