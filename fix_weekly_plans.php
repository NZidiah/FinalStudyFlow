<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
use App\Models\Course;
use App\Models\WeeklyPlan;
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$courses = Course::all();
foreach ($courses as $course) {
    $weeksCount = $course->duration_weeks ?? 16;
    for ($i = 1; $i <= $weeksCount; $i++) {
        $exists = WeeklyPlan::where('course_id', $course->id)->where('week_number', $i)->exists();
        if (!$exists) {
            $course->weeklyPlans()->create(['week_number' => $i, 'title' => "Week $i Content", 'completed' => false]);
            echo "Created Week $i for Course: {$course->title}\n";
        }
    }
}
echo "Finished!\n";
