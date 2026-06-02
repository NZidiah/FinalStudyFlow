<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            "message" => "Dashboard API Ready"
        ]);
    }

    public function stats(Request $request)
    {
        $user = $request->user(); // الحصول على الطالبة الحالية

        return response()->json([
            // المواد التي تدرس حالياً فقط
            "activeCourses" => Course::where('user_id', $user->id)
                ->where('status', 'in-progress')
                ->count(),
            
            // المهام التي لم تكتمل بعد
            "pendingTasks" => Task::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            
            // مجموع الساعات للمواد المكتملة (مع إضافة الساعات القديمة من ملف المستخدم)
            "completedCredits" => Course::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('credits') + $user->completed_credit_hours,
            
            "milestones" => 1
        ]);
    }

    public function academicSummary(Request $request)
    {
        $user = $request->user();
        $totalRequired = $user->total_credit_hours ?: 144;
        
        // حساب الساعات المجتازة فعلياً
        $passed = Course::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('credits') + $user->completed_credit_hours;

        // حساب النسبة المئوية
        $percentage = ($totalRequired > 0) ? ($passed / $totalRequired) * 100 : 0;

        return response()->json([
            "completionPercentage" => round($percentage, 1),
            "passedCredits" => $passed,
            "requiredCredits" => $totalRequired
        ]);
    }

    public function highPriorityTasks(Request $request)
    {
        $user = $request->user();

        // جلب آخر 5 مهام غير مكتملة ومرتبة حسب الأولوية أو التاريخ
        $tasks = Task::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return response()->json($tasks);
    }
}