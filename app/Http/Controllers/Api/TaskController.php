<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // 1. جلب كل مهام المستخدم المسجل دخوله حالياً (مع اسم الكورس إن وجد)
    public function index()
    {
        $tasks = Task::with('course')->where('user_id', Auth::id())->latest()->get();
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
                'dueDate'     => 'required|date',
                'dueTime'     => 'nullable',
                'is_recurring'     => 'nullable|boolean',
                'repeat_frequency' => 'nullable|string',
                'repeat_interval'  => 'nullable|integer',
            ]);

            $task = new \App\Models\Task();
            $task->user_id       = Auth::id();
            $task->course_id     = $request->course_id; // يقبل null الآن
            $task->week_number   = $request->week_number; // يقبل null الآن
            $task->title         = $request->title;
            $task->description   = $request->description;
            $task->priority      = $request->priority ?? 'medium';
            $task->type          = $request->type ?? 'study-task';
            $task->due_date      = $request->dueDate;
            $task->due_time      = $request->dueTime;

            // التذكير
            $task->reminder = $request->enableReminder ? 1 : 0;

            // مهم جداً: لا تثبّت reminder_value على 15 إلا لو القيمة غير موجودة فعلاً
            // لأن الفرونت قد يرسل timing بالـ minutes/hours/days بناءً على unit
            $task->reminder_value = $request->has('timing') && $request->timing !== null ? $request->timing : 15;
            $task->reminder_unit  = $request->has('unit') && $request->unit !== null ? $request->unit : 'minutes';

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
        $task = Task::where('user_id', Auth::id())->findOrFail($id);

        // حماية من مشكلة repeat_interval عندما يتم تعطيل التكرار أو لا يتم إرساله من الفرونت
        $payload = $request->all();

        // إذا is_recurring = false أو غير موجود في الريكويست، تأكد من وجود repeat_interval بقيمة صالحة
        $isRecurring = array_key_exists('is_recurring', $payload)
            ? (bool) $payload['is_recurring']
            : (bool) ($task->is_recurring ?? false);

        if (!$isRecurring) {
            if (!array_key_exists('repeat_interval', $payload) || $payload['repeat_interval'] === null) {
                $payload['repeat_interval'] = 1;
            }
            if (array_key_exists('repeat_frequency', $payload) && $payload['repeat_frequency'] === null) {
                $payload['repeat_frequency'] = null;
            }
        } else {
            // لو التكرار مفعّل لكن repeat_interval غير موجود/فارغ
            if (!array_key_exists('repeat_interval', $payload) || $payload['repeat_interval'] === null) {
                $payload['repeat_interval'] = 1;
            }
        }

        // لا تثبّت reminder_value على 15 في حالة update عندما تكون unit/timing تغيرت
        if (array_key_exists('reminder_value', $payload) && $payload['reminder_value'] === null) {
            unset($payload['reminder_value']);
        }
        if (array_key_exists('reminder_unit', $payload) && $payload['reminder_unit'] === null) {
            unset($payload['reminder_unit']);
        }

        // لا يتم تحديث status داخل payload لمنع أي تعارض، ويتم التعامل معه بعد update
        unset($payload['status']);

        $task->update($payload);

        // تحديث الحالة إذا تم إرسالها
        // ملاحظة: request يمرّ عبر mass-assignment وقد لا يصل القيمة بشكل صحيح في بعض الحالات
        // لذلك نضمن كتابة status صراحةً في النهاية.
        if ($request->has('status')) {
            // Frontend يرسل status كـ done / todo
            // Backend في tasks table يستخدم pending / completed
            $incomingStatus = $request->status;
            $normalizedStatus = $incomingStatus === 'done' ? 'completed' : ($incomingStatus === 'todo' ? 'pending' : $incomingStatus);

            $task->forceFill(['status' => $normalizedStatus])->save();
        }

        return response()->json($task->fresh()->load('course'));
    }

    public function toggle($id)
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($id);

        // التبديل الذكي
        $task->status = ($task->status === 'completed') ? 'pending' : 'completed';
        $task->save();

        return response()->json($task->load('course'));
    }



    // 4. حذف مهمة
    public function destroy($id)
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($id);
        $taskName = $task->title;
        $task->delete();

        // Removed Task Deleted notification to reduce noise

        return response()->json(['message' => 'Task deleted']);
    }
}
