<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->index();
        });
        Schema::table('audit_events', function (Blueprint $table) {
            $table->json('changes')->nullable();
            $table->index(['created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_events', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'id']);
            $table->dropColumn('changes');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('suspended_at');
        });
    }
};
