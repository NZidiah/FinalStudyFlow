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
    Schema::create('exam_topics', function (Blueprint $table) {
        $table->id();
        // يربط الموضوع بالامتحان (الامتحان هو أصلاً Task في جدول المهام)
        $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
        $table->string('title');
        $table->boolean('completed')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_topics');
    }
};
