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
        Schema::table('users', function (Blueprint $table) {
            // حقول التفضيلات التي ظهرت في صفحة Settings
            $table->json('reminder_preferences')->nullable()->after('major'); 
            $table->string('language')->default('en')->after('reminder_preferences');
            $table->string('theme')->default('light')->after('language');
            $table->integer('current_semester')->default(1)->after('academic_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reminder_preferences', 'language', 'theme', 'current_semester']);
        });
    }
};
