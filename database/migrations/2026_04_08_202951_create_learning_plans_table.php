<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('learning_plans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        $table->string('title'); // Plan Title
        $table->text('goal'); // Goal
        $table->text('description')->nullable(); // Description
        $table->string('category')->nullable(); // Category
        $table->string('target_skill')->nullable(); // Target Skill
        
        $table->date('start_date'); // Start Date
        $table->date('end_date')->nullable(); // End Date
        
        $table->string('status')->default('planned'); // Status (Planned, In Progress, Completed)
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_plans');
    }
};
