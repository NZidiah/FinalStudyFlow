<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeeklyPlan;
use App\Models\Notification;
use Illuminate\Http\Request;

class WeeklyPlanController extends Controller
{
    /**
     * تحديث حالة الأسبوع (مكتمل أو غير مكتمل)
     */
    public function update(Request $request, $id)
    {
        // البحث عن الأسبوع يدوياً للتأكد من المعرف
        $weeklyPlan = WeeklyPlan::with('course')->find($id);

        if (!$weeklyPlan) {
            return response()->json(['error' => 'Weekly plan not found in database', 'id' => $id], 404);
        }

        $request->validate([
            'completed' => 'required|boolean',
        ]);

        // تحديث الحقل بشكل صريح لضمان استمرارية البيانات
        $saved = $weeklyPlan->update(['completed' => $request->completed]);

        // إضافة إشعار عند إكمال الأسبوع بنجاح (فقط عند التحويل لـ TRUE وحفظه)
        if ($saved && $request->completed) {
            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'Weekly Progress!',
                'message' => "Congratulations! You finished Week {$weeklyPlan->week_number} in {$weeklyPlan->course->title}.",
                'type' => 'success',
                'target_route' => "/courses/{$weeklyPlan->course_id}"
            ]);
        }

        return response()->json([
            'status' => 'success',
            'saved' => $saved,
            'data' => $weeklyPlan->fresh()
        ]);
    }
}
