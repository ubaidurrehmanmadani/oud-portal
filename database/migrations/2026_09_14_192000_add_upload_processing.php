<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['workspace_items', 'report_submissions'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->boolean('file_processing_required')->default(false);
                $table->timestamp('file_processed_at')->nullable();
                $table->string('file_sha256', 64)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['workspace_items', 'report_submissions'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['file_processing_required', 'file_processed_at', 'file_sha256']));
        }
    }
};
