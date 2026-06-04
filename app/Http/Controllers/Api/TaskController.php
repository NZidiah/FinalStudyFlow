<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Notification;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // 1. جلب كل مهام المستخدم المسجل دخوله حالياً (مع اسم الكورس إن وجد)
    public function index()
    {
        $tasks = Task::with('course')->where('user_id', auth()->id())->latest()->get();
        return response()->json($tasks);
    }

    // 2. إضافة مهمة جديدة
// public function store(Request $request)
// {
//     // 1. التحقق من البيانات (تعديل المسميات لتطابق ما يرسله الفرونت إند)
//     $validated = $request->validate([
//         'title'       => 'required|string',
//         'course_id'   => 'required|exists:courses,id',
//         'week_number' => 'required|integer',
//         'priority'    => 'nullable|string', 
//         'type'        => 'nullable|string',
//         'dueDate'     => 'nullable', // الاسم من الفرونت إند
//         'dueTime'     => 'nullable',
//         'enableReminder' => 'nullable', 
//         'timing'      => 'nullable', // القيمة (15 مثلاً)
//         'unit'        => 'nullable', // الوحدة (minutes)
//     ]);

    
//     // 2. إنشاء المهمة
//     $task = Task::create([
//         'user_id'         => auth()->id(),
//         'course_id'       => $validated['course_id'],
//         'week_number'     => $validated['week_number'],
//         'title'           => $validated['title'],
//         'priority'        => $request->priority ?? 'medium', 
//         'type'            => $request->type ?? 'study-task',
        
//         // ربط المسميات (Database Column => Request Key)
//         'due_date'        => $request->dueDate, 
//         'due_time'        => $request->dueTime,
        
//         // ربط التذكير (تأكدي أن الأعمدة في الداتا بيز هي reminder_value و reminder_unit)
//         'reminder'        => $request->enableReminder ? 1 : 0,
// // إذا كانت timing فارغة، نضع 15
// // التعديل هنا: نتأكد من القيمة القادمة من الفرونت إند أولاً
//     'reminder_value'  => $request->has('timing') ? $request->timing : 15, 
//     'reminder_unit'   => $request->has('unit') ? $request->unit : 'minutes',      
//       'status'          => 'pending',
//     ]);

//     return response()->json([
//         'status' => 'success',
//         'data'   => $task 
//     ], 201);
// }
// في ملف TaskController.php
// public function store(Request $request) {
//     try {
//         $task = new Task();
        
//         // ربط البيانات يدويًا لضمان الدقة
//         $task->user_id     = auth()->id(); // ضروري جداً لأن الميغريشن تطلبه
//         $task->course_id   = $request->course_id;
//         $task->week_number = $request->week_number;
//         $task->title       = $request->title;
//         $task->type        = $request->type ?? 'study-task';
//         $task->priority    = $request->priority ?? 'medium';
//         $task->status      = 'pending';
        
//         // معالجة التذكير بناءً على مسميات الفرونت إند
//         $task->reminder       = $request->enableReminder ? 1 : 0;
// // Use 'timing' as main source, fallback to 15
// $task->reminder_value = $request->input('timing', 15);

// // Use 'unit' as main source, fallback to 'minutes'
// $task->reminder_unit  = $request->input('unit', 'minutes');
        
//         // التواريخ
//         $task->due_date = $request->dueDate;
//         $task->due_time = $request->dueTime;

//         $task->save();

//         return response()->json($task, 201);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }
// public function store(Request $request)
// {
//     $request->validate([
//         'title' => 'required|string',
//         'course_id' => 'required|exists:courses,id',
//         'week_number' => 'required|integer',
//         'type' => 'nullable|string',
//         'priority' => 'nullable|string',
//         'dueDate' => 'nullable|date',
//         'dueTime' => 'nullable|string',
//         'enableReminder' => 'nullable|boolean',
//         'timing' => 'nullable|integer|min:0',
//         'unit' => 'nullable|string|max:10',
//     ]);

//     $task = new Task();
//     $task->user_id = auth()->id();
//     $task->course_id = $request->course_id;
//     $task->week_number = $request->week_number;
//     $task->title = $request->title;
//     $task->type = $request->type ?? 'study-task';
//     $task->priority = $request->priority ?? 'medium';
//     $task->due_date = $request->dueDate;
//     $task->due_time = $request->dueTime;
// $task->reminder       = $request->enableReminder ? 1 : 0;
// $task->reminder_value = $request->timing ?? 15;
// $task->reminder_unit  = $request->unit ?? 'minutes';
//     $task->status = 'pending';
//     $task->save();

//     // إعادة تحميل المهمة من الداتا بيز للتأكد من القيم
//     return response()->json($task->fresh(), 201);
// }
public function store(Request $request)
{
    try {
        // التحقق من البيانات
        $validated = $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'course_id'   => 'nullable|exists:courses,id',
            'week_number' => 'nullable|integer',
            'dueDate'     => 'nullable',
            'dueTime'     => 'nullable',
            'is_recurring'     => 'nullable|boolean',
            'repeat_frequency' => 'nullable|string',
            'repeat_interval'  => 'nullable|integer',
        ]);

        $task = new \App\Models\Task();
        $task->user_id       = auth()->id();
        $task->course_id     = $request->course_id; // يقبل null الآن
        $task->week_number   = $request->week_number; // يقبل null الآن
        $task->title         = $request->title;
        $task->description   = $request->description;
        $task->priority      = $request->priority ?? 'medium';
        $task->type          = $request->type ?? 'study-task';
        $task->due_date      = $request->dueDate;
        $task->due_time      = $request->dueTime;

        // التذكير
        $task->reminder       = $request->enableReminder ? 1 : 0;
        $task->reminder_value = $request->timing ?? 15;
        $task->reminder_unit  = $request->unit ?? 'minutes';

        $task->status = 'pending';

        // التكرار
        $task->is_recurring     = $request->is_recurring ?? false;
        $task->repeat_frequency = $request->repeat_frequency;
        $task->repeat_interval  = $request->repeat_interval ?? 1;

        $task->save();

        // Removed Task Created notification to reduce noise

        // إعادة تحميل المهمة مع الكورس ليظهر في الفرونت إند فوراً
        return response()->json($task->load('course'), 201);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    // 3. تحديث المهمة (زر الصح وتغيير العنوان)
    // public function update(Request $request, $id)
    // {
    //     // التأكد أن المهمة تخص المستخدم الحالي
    //     $task = Task::where('user_id', auth()->id())->findOrFail($id);

    //     // إذا أرسلنا status (جاهزة من الفرونت إند كـ completed أو pending)
    //     if ($request->has('status')) {
    //         $task->status = $request->status;
    //     }

    //     // إذا أرسلنا completed (كـ boolean) للتحويل اليدوي
    //     if ($request->has('completed')) {
    //         $task->status = $request->completed ? 'completed' : 'pending';
    //     }

    //     // تحديث العنوان (title)
    //     if ($request->has('title')) {
    //         $task->title = $request->title;
    //     }

    //     $task->save();

    //     return response()->json($task);
    // }


    public function update(Request $request, $id)
{
    $task = Task::where('user_id', auth()->id())->findOrFail($id);

    // تحديث كل الحقول المتاحة
    if ($request->has('status'))      $task->status = $request->status;
    if ($request->has('completed'))   $task->status = $request->completed ? 'completed' : 'pending';
    if ($request->has('title'))       $task->title = $request->title;
    if ($request->has('description')) $task->description = $request->description;
    if ($request->has('priority'))    $task->priority = $request->priority;
    if ($request->has('type'))        $task->type = $request->type;
    
    // التواريخ
    if ($request->has('dueDate'))     $task->due_date = $request->dueDate;
    if ($request->has('dueTime'))     $task->due_time = $request->dueTime;
    
    // التذكير
    if ($request->has('enableReminder')) {
        $task->reminder       = $request->enableReminder ? 1 : 0;
        $task->reminder_value = $request->timing ?? 15;
        $task->reminder_unit  = $request->unit ?? 'minutes';
    }

    // التكرار
    if ($request->has('is_recurring'))     $task->is_recurring = $request->is_recurring;
    if ($request->has('repeat_frequency')) $task->repeat_frequency = $request->repeat_frequency;
    if ($request->has('repeat_interval'))  $task->repeat_interval = $request->repeat_interval;

    $task->save();

    // إضافة إشعار عند إكمال المهمة
    if ($task->status === 'completed' && ($request->status === 'completed' || $request->completed)) {
        Notification::create([
            'user_id' => auth()->id(),
            'title' => 'Task Completed!',
            'message' => "Great job! You finished: {$task->title}",
            'type' => 'success',
            'target_route' => $task->course_id ? "/courses/{$task->course_id}" : "/tasks"
        ]);
    }

    return response()->json($task->load('course'));
}


    // 4. حذف مهمة
    public function destroy($id)
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($id);
        $taskName = $task->title;
        $task->delete();

        // Removed Task Deleted notification to reduce noise

        return response()->json(['message' => 'Task deleted']);
    }
}