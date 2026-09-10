<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspace_items', function (Blueprint $table) {
            $table->date('report_month')->nullable();
            $table->json('financial_data')->nullable();
            $table->unique(['property_id', 'report_month']);
        });
    }

    public function down(): void
    {
        Schema::table('workspace_items', function (Blueprint $table) {
            $table->dropUnique(['property_id', 'report_month']);
            $table->dropColumn(['report_month', 'financial_data']);
        });
    }
};
