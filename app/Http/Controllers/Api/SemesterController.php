<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SemesterController extends Controller
{
    /**
     * جلب كافة الفصول الدراسية الخاصة بالمستخدم الحالي مع موادها.
     */
    public function index(Request $request)
    {
        return $request->user()
            ->semesters()
            ->with('courses')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * حفظ فصل دراسي جديد.
     */
    public function store(Request $request)
    {
        // تسجيل البيانات القادمة للمساعدة في التتبع (Debug)
        Log::info('طلب إضافة فصل جديد:', $request->all());

        try {
            // توحيد حالة الأحرف للحالة (Status) لتطابق شروط التحقق وقاعدة البيانات
            if ($request->has('status')) {
                $request->merge([
                    'status' => strtolower($request->status)
                ]);
            }

            $data = $request->validate([
                'name'          => 'required|string|max:255',
                'academic_year' => 'required|string|max:50',
                'num_of_weeks'  => 'nullable|integer|min:1|max:20',
                'status'        => 'required|string|in:planned,current,completed',
            ]);

            // إنشاء الفصل وربطه بالمستخدم تلقائياً عبر العلاقة
            $semester = $request->user()->semesters()->create($data);

            // إضافة إشعار
            Notification::create([
                'user_id' => $request->user()->id,
                'title' => 'New Semester Added',
                'message' => "Successfully added semester: {$semester->name}",
                'type' => 'success',
                'target_route' => '/academic-planning'
            ]);

            return response()->json($semester, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('خطأ في التحقق من البيانات:', $e->errors());
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('خطأ غير متوقع عند الحفظ:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Server Error'], 500);
        }
    }

    /**
     * تحديث بيانات فصل دراسي موجود.
     */
    public function update(Request $request, Semester $semester)
    {
        // التحقق من ملكية الفصل
        if ($request->user()->id !== $semester->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($request->has('status')) {
            $request->merge([
                'status' => strtolower($request->status)
            ]);
        }

        $data = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'academic_year' => 'sometimes|required|string|max:50',
            'num_of_weeks'  => 'nullable|integer|min:1|max:20',
            'status'        => 'sometimes|required|string|in:planned,current,completed',
        ]);

        $semester->update($data);

        // إضافة إشعار
        Notification::create([
            'user_id' => $request->user()->id,
            'title' => 'Semester Updated',
            'message' => "Details for {$semester->name} have been updated.",
            'type' => 'info',
            'target_route' => '/academic-planning'
        ]);

        return response()->json($semester);
    }

    /**
     * حذف فصل دراسي.
     */
    public function destroy(Request $request, Semester $semester)
    {
        // التحقق من ملكية الفصل
        if ($request->user()->id !== $semester->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // سيتم حذف المواد المرتبطة تلقائياً إذا تم تفعيل Cascade Delete في الميجريشن
        $semesterName = $semester->name;
        $semester->delete();

        // إضافة إشعار عند الحذف
        \App\Models\Notification::create([
            'user_id' => $request->user()->id,
            'title' => 'Semester Deleted',
            'message' => "Removed: {$semesterName} and all its courses.",
            'type' => 'warning'
        ]);

        return response()->json(['message' => 'Semester deleted successfully'], 200);
    }
}