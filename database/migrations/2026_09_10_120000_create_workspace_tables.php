<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
        });
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('type')->nullable();
            $table->unsignedInteger('total_units')->default(0);
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('property_user', function (Blueprint $table) {
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['property_id', 'user_id']);
        });
        Schema::create('workspace_items', function (Blueprint $table) {
            $table->id();
            $table->string('kind')->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('category')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('audience')->default('staff');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('status')->default('published');
            $table->timestamp('published_at')->nullable();
            $table->string('period')->nullable();
            $table->decimal('occupancy', 5, 2)->nullable();
            $table->decimal('net_revenue', 16, 2)->nullable();
            $table->decimal('leased_area', 14, 2)->nullable();
            $table->decimal('amount', 16, 2)->nullable();
            $table->text('decision_comment')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index(['kind', 'property_id', 'status']);
            $table->index(['kind', 'department_id', 'audience']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_items');
        Schema::dropIfExists('property_user');
        Schema::dropIfExists('properties');
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('department_id'));
        Schema::dropIfExists('departments');
    }
};
