<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

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
     * إضافة مادة جديدة - تم تحديث الـ Validation وتأمين الحقول النصية
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'credits'        => 'required|integer|min:1',
            'status'         => 'required|string|in:planned,in-progress,completed',
            'semester_id'    => [
                'nullable',
                Rule::exists('semesters', 'id')->where(fn($query) => $query->where('user_id', $user->id)),
            ],
            'code'           => 'nullable|string|max:50',
            'instructor'     => 'nullable|string|max:255',
            'duration_weeks' => 'nullable|integer|min:1',
            'description'    => 'nullable|string',
            'image_url'      => 'nullable|string',
            'numeric_grade'  => 'nullable|numeric|min:0|max:100',
        ]);
        $data['user_id'] = $user->id;
        // الحفظ التلقائي مع ربط المادة بالـ user_id الخاص بالطالبة المسجلة
        $course = Course::create($data);
        // --- إنشاء الأسابيع تلقائياً (اختياري إذا الجدول متاح) ---
        if (Schema::hasTable('weekly_plans')) {
            $weeksCount = $course->duration_weeks ?? 16;
            for ($i = 1; $i <= $weeksCount; $i++) {
                $course->weeklyPlans()->create([
                    'week_number' => $i,
                    'title'       => "Week $i Content",
                    'completed'   => false
                ]);
            }
        }

        $this->safeCreateNotification([
            'user_id' => $user->id,
            'title' => 'New Course Added',
            'message' => "Successfully created course: {$course->title}",
            'type' => 'success',
            'target_route' => "/courses/{$course->id}"
        ]);

        return response()->json([
            'message' => 'Course created successfully with weekly plans',
            'course'  => Schema::hasTable('weekly_plans') ? $course->load('weeklyPlans') : $course
        ], 201);
    }

    /**
     * تحديث بيانات المادة - دعم تحديث الحقول الجديدة أيضاً
     */
    public function update(Request $request, Course $course)
    {
        $user = $request->user();

        // التأكد أن المادة تخص الطالبة الحالية
        if ($course->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }


        $data = $request->validate([
            'title'          => 'sometimes|string|max:255',
            'credits'        => 'sometimes|integer|min:1',
            'status'         => 'sometimes|string|in:planned,current,completed',
            'semester_id'    => [
                'nullable',
                Rule::exists('semesters', 'id')->where(fn($query) => $query->where('user_id', $user->id)),
            ],
            'code'           => 'nullable|string|max:50',
            'instructor'     => 'nullable|string|max:255',
            'duration_weeks' => 'nullable|integer|min:1',
            'description'    => 'nullable|string',
            'image_url'      => 'nullable|string',
            'numeric_grade'  => 'nullable|numeric|min:0|max:100',
        ]);

        $course->update($data);

        $this->safeCreateNotification([
            'user_id' => $user->id,
            'title' => 'Course Updated',
            'message' => "The details for {$course->title} have been updated.",
            'type' => 'info',
            'target_route' => "/courses/{$course->id}"
        ]);

        return response()->json([
            'message' => 'Course updated successfully',
            'course'  => $course
        ]);
    }

    /**
     * حذف المادة نهائياً
     */
    public function destroy(Course $course)
    {
        $user = request()->user();

        // التأكد من الصلاحية قبل الحذف
        if ($course->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $courseName = $course->title;
        $course->delete();

        $this->safeCreateNotification([
            'user_id' => $user->id,
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
        $course = Course::where('user_id', request()->user()->id)->find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        // --- إنشاء الأسابيع إذا كانت ناقصة (للمواد القديمة) ---
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

    /**
     * دالة تنظيف وتوحيد البيانات القادمة من الفرونت اند ومنع أخطاء الـ UUID
     */
    private function normalizeCourseInput(Request $request): void
    {
        $aliases = [
            'semesterId' => 'semester_id',
            'imageUrl' => 'image_url',
            'durationWeeks' => 'duration_weeks',
            'numericGrade' => 'numeric_grade',
        ];

        $normalized = [];
        foreach ($aliases as $from => $to) {
            if ($request->has($from) && ! $request->has($to)) {
                $value = $request->input($from);
                $normalized[$to] = $value;
            }
        }

        // معالجة حقل الستاتس ليكون حروف صغيرة دائماً ليتوافق مع قاعدة البيانات
        if ($request->has('status')) {
            $normalized['status'] = strtolower($request->input('status'));
        }

        // دمج التعديلات الأولية
        if ($normalized !== []) {
            $request->merge($normalized);
        }

        // الفحص الصارم والنهائي: لمنع الـ 500 ومشاكل الـ UUID النصي في حقل الـ semester_id
        $finalSemesterId = $request->input('semester_id');
        if ($finalSemesterId !== null) {
            if ($finalSemesterId === 'prior-completed' || !is_numeric($finalSemesterId)) {
                $request->merge(['semester_id' => null]);
            }
        }
    }

    /**
     * Keep course endpoints working even if optional tables are not migrated yet.
     */
    private function safeCreateNotification(array $payload): void
    {
        try {
            if (Schema::hasTable('notifications')) {
                Notification::create($payload);
            }
        } catch (Throwable $e) {
            Log::warning('Notification creation skipped in CourseController', [
                'error' => $e->getMessage(),
                'course_id' => $payload['target_route'] ?? null,
            ]);
        }
    }
}
