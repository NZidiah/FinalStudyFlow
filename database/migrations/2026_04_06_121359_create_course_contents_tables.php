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
    // جدول الأسابيع
    Schema::create('weekly_plans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('course_id')->constrained()->cascadeOnDelete();
        $table->integer('week_number');
        $table->string('title')->nullable();
        $table->boolean('completed')->default(false);
        $table->timestamps();
    });

    // جدول المهام (Study Tasks)
    Schema::create('study_tasks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('weekly_plan_id')->constrained()->cascadeOnDelete();
        $table->string('title');
        $table->boolean('completed')->default(false);
        $table->timestamps();
    });

    // جدول الواجبات (Assignments)
    Schema::create('assignments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('weekly_plan_id')->constrained()->cascadeOnDelete();
        $table->string('title');
        $table->date('due_date')->nullable();
        $table->string('status')->default('pending'); // pending, submitted, graded
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('study_tasks');
        Schema::dropIfExists('weekly_plans');
    }
};
