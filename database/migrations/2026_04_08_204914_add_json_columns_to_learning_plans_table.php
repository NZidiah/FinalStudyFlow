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
        Schema::table('learning_plans', function (Blueprint $table) {
            $table->json('stages')->nullable();
            $table->json('milestones')->nullable();
            $table->json('resources')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_plans', function (Blueprint $table) {
            $table->dropColumn(['stages', 'milestones', 'resources']);
        });
    }
};
