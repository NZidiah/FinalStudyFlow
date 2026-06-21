<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamTopic;
use App\Models\Notification;
use Illuminate\Http\Request;

class ExamTopicController extends Controller
{
    // جلب كل المواضيع الخاصة بامتحان معين ضمن كورس معين
    public function index($courseId, $taskId)
    {
        return ExamTopic::where('task_id', $taskId)->get();
    }

    // إضافة موضوع جديد لامتحان معين
    public function store(Request $request, $courseId, $taskId)
    {
        $validated = $request->validate([
            'title'   => 'required|string',
        ]);

        $topic = ExamTopic::create([
            'task_id' => $taskId,
            'title'   => $validated['title'],
            'completed' => false
        ]);

        // إضافة إشعار
        Notification::create([
            'user_id' => auth()->id(),
            'title' => 'Exam Topic Added',
            'message' => "Added \"{$topic->title}\" to your exam preparation list.",
            'type' => 'info'
        ]);

        return response()->json($topic, 201);
    }

    // تبديل حالة الانتهاء (صح أو خطأ)
    public function toggle($id)
    {
        $topic = ExamTopic::findOrFail($id);
        $topic->completed = !$topic->completed;
        $topic->save();

        if ($topic->completed) {
            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'Topic Completed',
                'message' => "Mastered: {$topic->title}",
                'type' => 'success'
            ]);
        }

        return $topic;
    }

    // تحديث عنوان الموضوع
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string',
        ]);

        $topic = ExamTopic::findOrFail($id);
        $topic->update($validated);
        return $topic;
    }

    // حذف موضوع
    public function destroy($id)
    {
        ExamTopic::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
