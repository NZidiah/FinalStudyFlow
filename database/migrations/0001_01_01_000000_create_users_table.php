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
        Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');

// 🎓 التعديلات لتطابق صفحة الـ Setup تماماً:
    $table->string('academic_year')->nullable(); // للسنة الدراسية (Step 1)
    $table->integer('total_credit_hours')->default(144); // للساعات الكلية (Step 2)
    $table->integer('completed_credit_hours')->default(0); // للساعات المنجزة (Step 2)
    $table->float('current_gpa')->default(0); // للمعدل الحالي (Step 3)
    $table->string('university')->nullable(); // لاسم الجامعة (Step 4)
    $table->string('major')->nullable(); // للتخصص (Step 4)
    // حقل إضافي ليعرف النظام أن المستخدم أنهى الإعدادات
    $table->boolean('onboarding_completed')->default(false);
    
    $table->rememberToken();
    $table->timestamps();
});
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
