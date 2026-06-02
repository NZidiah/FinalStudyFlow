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
    Schema::create('resources', function (Blueprint $table) {
        $table->id();
        $table->morphs('resourceable');
        $table->string('title');
        $table->string('type'); // Link, PDF, Video, etc.
        $table->text('url');   // سيخزن الرابط أو مسار الملف المرفوع
        $table->text('description')->nullable(); // حقل اختياري كما في الصورة
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
