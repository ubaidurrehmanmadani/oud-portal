<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspace_items', fn (Blueprint $table) => $table->string('target_mode')->default('legacy'));
        foreach (['user', 'department', 'property'] as $kind) {
            Schema::create('announcement_'.$kind, function (Blueprint $table) use ($kind) {
                $table->foreignId('workspace_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId($kind.'_id')->constrained()->restrictOnDelete();
                $table->primary(['workspace_item_id', $kind.'_id']);
                $table->index([$kind.'_id', 'workspace_item_id']);
            });
        }
        Schema::table('users', fn (Blueprint $table) => $table->json('permission_overrides')->nullable());
    }

    public function down(): void
    {
        foreach (['user', 'department', 'property'] as $kind) {
            Schema::dropIfExists('announcement_'.$kind);
        }
        Schema::table('workspace_items', fn (Blueprint $table) => $table->dropColumn('target_mode'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('permission_overrides'));
    }
};
