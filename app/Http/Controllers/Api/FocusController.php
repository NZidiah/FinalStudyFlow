<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FocusSession;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // أضفنا هذا لمراقبة الأخطاء في سجلات لارفيل

class FocusController extends Controller
{
    public function store(Request $request)
    {
        // 1. التحقق من صحة البيانات القادمة من الفرونت إند
        $request->validate([
            'minutes' => 'required|integer',
            'type'    => 'nullable|string',
            'task_id' => 'nullable|exists:tasks,id',

        ]);

        try {
            // 2. التحقق من وجود مستخدم مسجل دخول (عبر التوكن)
            if (!Auth::check()) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            // 3. إنشاء السجل وربطه بالـ user_id الصحيح
            $session = FocusSession::create([
                'user_id' => Auth::id(),
                'minutes' => $request->minutes,
                'type' => $request->type ?? 'pomodoro',
                'task_id' => $request->task_id, // سيأخذ القيمة سواء كانت موجودة أو null
            ]);

            // إضافة إشعار
            Notification::create([
                'user_id' => Auth::id(),
                'title' => 'Focus Session Saved',
                'message' => "Great job! You focused for {$session->minutes} minutes.",
                'type' => 'success',
                'target_route' => '/dashboard'
            ]);

            return response()->json([
                'message' => 'Focus session saved successfully!',
                'session' => $session
            ], 201);
        } catch (\Exception $e) {
            // 4. في حال حدوث خطأ (مثلاً مشكلة في قاعدة البيانات)، سيظهر لكِ في ملف laravel.log
            Log::error('Focus Session Save Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to save session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // دالة لجلب إجمالي دقائق التركيز لليوم
    public function dailyStats()
    {
        // نستخدم Auth::id() لضمان جلب بيانات المستخدم الحالي فقط
        $totalMinutes = FocusSession::where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->sum('minutes');

        return response()->json([
            'total_minutes' => (int) $totalMinutes
        ]);
    }
}
