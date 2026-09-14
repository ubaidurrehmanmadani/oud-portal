<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_submissions', function (Blueprint $table) {
            $table->foreignId('published_item_id')->nullable()->unique()->constrained('workspace_items')->restrictOnDelete();
            $table->index(['status', 'submitted_at']);
        });
        Schema::create('report_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_submission_id')->constrained()->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->string('decision');
            $table->text('comment')->nullable();
            $table->json('snapshot');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_reviews');
        Schema::table('report_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('published_item_id');
            $table->dropIndex(['status', 'submitted_at']);
        });
    }
};
