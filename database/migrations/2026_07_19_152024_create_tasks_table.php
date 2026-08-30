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
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained()->onDelete('cascade');
        // Pwedeng nullable kung hindi pa agad nai-a-assign sa Team Member ang task
        $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
        $table->string('title');
        $table->text('description')->nullable();
        // Strict status indicator
        $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
