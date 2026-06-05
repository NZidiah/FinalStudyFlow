<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Notification;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * جلب كل المواد الخاصة بالطالبة المسجلة حالياً مع بيانات الفصل الدراسي المرتبط
     */
    public function index(Request $request) 
    {
        return $request->user()->courses()->with('semester')->get();
    }

    /**
     * إضافة مادة جديدة - تم تحديث الـ Validation لاستقبال الحقول الجديدة من الواجهة
     */
    public function store(Request $request) 
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'credits'        => 'required|integer|min:1',
            'status'         => 'required|string|in:planned,current,completed', 
            'semester_id'    => 'nullable|exists:semesters,id',
            'code'           => 'nullable|string|max:50',
            'instructor'     => 'nullable|string|max:255',
            'duration_weeks' => 'nullable|integer|min:1',
            'description'    => 'nullable|string',
            'image_url'      => 'nullable|string',
            'numeric_grade'  => 'nullable|numeric|min:0|max:100',
        ]);

        // الحفظ التلقائي مع ربط المادة بالـ user_id الخاص بالطالبة المسجلة
        $course = $request->user()->courses()->create($data);

        // --- الجزء الجديد: إنشاء الأسابيع تلقائياً ---
        $weeksCount = $course->duration_weeks ?? 16;
        for ($i = 1; $i <= $weeksCount; $i++) {
            $course->weeklyPlans()->create([
                'week_number' => $i,
                'title'       => "Week $i Content",
                'completed'   => false
            ]);
        }

        // إضافة إشعار
        Notification::create([
            'user_id' => auth()->id(),
            'title' => 'New Course Added',
            'message' => "Successfully created course: {$course->title}",
            'type' => 'success',
            'target_route' => "/courses/{$course->id}"
        ]);

        return response()->json([
            'message' => 'Course created successfully with weekly plans',
            'course'  => $course->load('weeklyPlans')
        ], 201);
    }

    /**
     * تحديث بيانات المادة - دعم تحديث الحقول الجديدة أيضاً
     */
    public function update(Request $request, Course $course) 
    {
        // التأكد أن المادة تخص الطالبة الحالية
        if ((int)$course->user_id !== (int)auth()->id()) {
            return response()->json([
                'error'          => 'Unauthorized',
                'course_user_id' => $course->user_id,
                'auth_user_id'   => auth()->id(),
            ], 403);
        }

        $data = $request->validate([
            'title'          => 'sometimes|string|max:255',
            'credits'        => 'sometimes|integer|min:1',
            'status'         => 'sometimes|string|in:planned,current,completed',
            'semester_id'    => 'nullable|exists:semesters,id',
            'code'           => 'nullable|string|max:50',
            'instructor'     => 'nullable|string|max:255',
            'duration_weeks' => 'nullable|integer|min:1',
            'description'    => 'nullable|string',
            'image_url'      => 'nullable|string',
            'numeric_grade'  => 'nullable|numeric|min:0|max:100',
            'weekly_plan'    => 'nullable|array',
            'resources'      => 'nullable|array',
        ]);

        // Don't overwrite semester_id if not explicitly sent or sent as null with existing value
        if (array_key_exists('semester_id', $data) && is_null($data['semester_id']) && !is_null($course->semester_id)) {
            unset($data['semester_id']);
        }

        $course->update($data);

        // Only notify on actual metadata changes (not on weekly_plan/resources-only updates)
        $metaFields = ['title','credits','status','code','instructor','duration_weeks','description','image_url','numeric_grade'];
        $hasMetaChange = count(array_intersect_key($data, array_flip($metaFields))) > 0;
        if ($hasMetaChange) {
            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'Course Updated',
                'message' => "Details for {$course->title} have been updated.",
                'type' => 'info',
                'target_route' => "/courses/{$course->id}"
            ]);
        }

        return response()->json([
            'message' => 'Course updated successfully',
            'course'  => $course->fresh(),
        ]);
    }

    /**
     * حذف المادة نهائياً
     */
    public function destroy(Course $course) 
    {
        // التأكد من الصلاحية قبل الحذف
        if ((int)$course->user_id !== (int)auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $courseName = $course->title;
        $course->delete();
        
        // إضافة إشعار عند الحذف
        Notification::create([
            'user_id' => auth()->id(),
            'title' => 'Course Deleted',
            'message' => "Successfully removed: {$courseName}",
            'type' => 'warning'
        ]);

        return response()->json([
            'message' => 'Course deleted successfully'
        ], 200);
    }

    public function show($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // --- الجزء التلقائي: إنشاء الأسابيع إذا كانت ناقصة (للمواد القديمة) ---
        $weeksCount = $course->duration_weeks ?? 16;
        $existingWeeks = $course->weeklyPlans()->pluck('week_number')->toArray();
        
        for ($i = 1; $i <= $weeksCount; $i++) {
            if (!in_array($i, $existingWeeks)) {
                $course->weeklyPlans()->create([
                    'week_number' => $i,
                    'title'       => "Week $i Content",
                    'completed'   => false
                ]);
            }
        }

        try {
            $course->load(['weeklyPlans', 'tasks']); 
            return response()->json(['status' => 'success', 'course' => $course]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}