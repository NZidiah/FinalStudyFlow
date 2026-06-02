<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningPlan;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    /**
     * عرض جميع خطط التعلم الخاصة بالمستخدم
     */
    public function index()
    {
        $plans = LearningPlan::with('resources')
            ->where('user_id', Auth::id())
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json($plans);
    }

    /**
     * حفظ خطة تعلم جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'goal' => 'required|string',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'target_skill' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:planned,active,completed,paused',
            'stages' => 'nullable|array',
            'milestones' => 'nullable|array',
        ]);

        // إضافة id المستخدم للبيانات قبل الحفظ
        $plan = LearningPlan::create(array_merge($validated, [
            'user_id' => Auth::id()
        ]));

        // إضافة إشعار
        Notification::create([
            'user_id' => Auth::id(),
            'title' => 'Learning Plan Created',
            'message' => "Successfully initiated your goal: {$plan->title}",
            'type' => 'success',
            'target_route' => '/self-learning'
        ]);

        return response()->json([
            'message' => 'Success! Your learning plan has been created.',
            'plan' => $plan
        ], 201);
    }

    /**
     * عرض تفاصيل خطة معينة
     */
    public function show($id)
    {
        $plan = LearningPlan::with('resources')->where('user_id', Auth::id())->findOrFail($id);
        return response()->json($plan);
    }

    /**
     * تحديث خطة موجودة
     */
    public function update(Request $request, $id)
    {
        $plan = LearningPlan::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'goal' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'target_skill' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|required|in:planned,active,completed,paused',
            'stages' => 'nullable|array',
            'milestones' => 'nullable|array',
        ]);

        $plan->update($validated);

        // Notify only if the plan status has just changed to 'completed'
        if ($request->status === 'completed' && $plan->status === 'completed') {
            Notification::create([
                'user_id' => Auth::id(),
                'title' => 'Learning Goal Achieved! 🏆',
                'message' => "Congratulations! You've officially completed: {$plan->title}",
                'type' => 'success',
                'target_route' => '/self-learning'
            ]);
        }

        return response()->json([
            'message' => 'Plan updated successfully',
            'plan' => $plan
        ]);
    }

    /**
     * حذف خطة
     */
    public function destroy($id)
    {
        $plan = LearningPlan::where('user_id', Auth::id())->findOrFail($id);
        $planTitle = $plan->title;
        $plan->delete();

        // Removed delete notification to reduce noise

        return response()->json([
            'message' => 'Plan deleted successfully'
        ]);
    }
}