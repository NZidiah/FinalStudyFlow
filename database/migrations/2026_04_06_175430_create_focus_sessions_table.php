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
    Schema::create('focus_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        // الربط مع جدول المهام
        // nullable() تعني أن الحقل اختياري، فإذا لم يختر المستخدم تاسك، سيبقى الحقل فارغاً وتخزن الجلسة بشكل عام
        $table->foreignId('task_id')->nullable()->constrained()->onDelete('set null');
        $table->integer('minutes'); // عدد الدقائق التي قضاها في التركيز
        $table->string('type')->default('pomodoro'); // نوع الجلسة
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('focus_sessions');
    }
};
