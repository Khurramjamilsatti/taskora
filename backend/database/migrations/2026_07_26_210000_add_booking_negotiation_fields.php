<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->decimal('customer_budget', 12, 2)->nullable()->after('payload');
            $table->decimal('current_offer', 12, 2)->nullable()->after('customer_budget');
            $table->string('current_offer_by', 20)->nullable()->after('current_offer');
            $table->decimal('deal_amount', 12, 2)->nullable()->after('current_offer_by');
            $table->json('offers')->nullable()->after('deal_amount');
            $table->timestamp('quoted_at')->nullable()->after('accepted_at');
            $table->timestamp('deal_accepted_at')->nullable()->after('quoted_at');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'customer_budget',
                'current_offer',
                'current_offer_by',
                'deal_amount',
                'offers',
                'quoted_at',
                'deal_accepted_at',
            ]);
        });
    }
};
