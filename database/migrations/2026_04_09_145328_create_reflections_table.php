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
        Schema::create('reflections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('title'); // Entry Title
            $table->date('date');    // Date of reflection
            $table->string('mood');  // Excellent, Good, Neutral, Tired, Stressed, Sad
            
            // محتوى المراجعة
            $table->text('achievements')->nullable(); // What did you achieve?
            $table->text('difficulties')->nullable(); // What was difficult?
            $table->text('learnings')->nullable();     // What did you learn?
            $table->text('improvements')->nullable();  // What to improve next?
            
            // إضافات اختيارية
            $table->text('gratitude')->nullable();     // Gratitude Note
            $table->json('tags')->nullable();          // Tags (Array)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reflections');
    }
};
