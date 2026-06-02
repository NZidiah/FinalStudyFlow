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
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('reminder_unit');
            $table->string('repeat_frequency')->nullable()->after('is_recurring'); // daily, weekly, monthly
            $table->integer('repeat_interval')->default(1)->after('repeat_frequency'); // كل كم يوم/أسبوع
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'repeat_frequency', 'repeat_interval']);
        });
    }
};
