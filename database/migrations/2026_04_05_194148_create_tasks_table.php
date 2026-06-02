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

            // ربط المهمة بالمستخدم
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // ربط المهمة بالكورس
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');

            // رقم الأسبوع
            $table->integer('week_number')->nullable();

            // عنوان المهمة
            $table->string('title');

            // نوع المهمة
            $table->string('type')->default('study-task');

            // الأولوية
            $table->string('priority')->default('medium');

            // الحالة
            $table->string('status')->default('pending');

            // تاريخ ووقت الاستحقاق
            $table->date('due_date')->nullable();
            $table->time('due_time')->nullable();

            // التذكير
            $table->boolean('reminder')->default(false);
            $table->integer('reminder_value')->default(15);
            $table->string('reminder_unit')->default('minutes');

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