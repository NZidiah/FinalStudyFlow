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
    Schema::create('courses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('semester_id')->nullable()->constrained()->cascadeOnDelete();           
        $table->string('title');            // اسم المادة
        $table->string('code')->nullable(); // كود المادة (جديد)
        $table->string('instructor')->nullable(); // اسم الدكتور (جديد)
        $table->integer('credits');         // عدد الساعات
        $table->integer('duration_weeks')->default(16); // عدد الأسابيع (جديد)
        $table->text('description')->nullable(); // الوصف أو الملاحظات (جديد)
        $table->longText('image_url')->nullable(); // رابط الصورة (جديد)
        $table->float('numeric_grade')->nullable(); 
        $table->string('status');           // planned / in-progress / completed
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
