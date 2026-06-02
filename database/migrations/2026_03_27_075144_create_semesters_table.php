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
Schema::create('semesters', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // الفصل الأول، الثاني.. الخ
        $table->string('academic_year'); // مثال: 2024/2025
        $table->integer('num_of_weeks')->default(16);
        $table->string('status'); // Planned, Current, Completed
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
