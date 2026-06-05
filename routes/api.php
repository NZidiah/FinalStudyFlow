<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SemesterController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FocusController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\ExamTopicController;
use App\Http\Controllers\Api\LearningController;
use App\Http\Controllers\Api\ReflectionController;
use App\Http\Controllers\Api\WeeklyPlanController;
use App\Http\Controllers\Api\NotificationController;

// --- Diagnostic endpoints (temporary) ---
Route::get('/health', function () {
    $tables = ['users','courses','tasks','focus_sessions','reflections','learning_plans','semesters','notifications'];
    $status = [];
    foreach ($tables as $table) {
        try {
            $count = \Illuminate\Support\Facades\DB::table($table)->count();
            $status[$table] = ['exists' => true, 'rows' => $count];
        } catch (\Exception $e) {
            $status[$table] = ['exists' => false, 'error' => $e->getMessage()];
        }
    }
    return response()->json(['db' => $status, 'auth_user' => auth()->id()]);
});

// Test authenticated save (requires Bearer token)
Route::middleware('auth:sanctum')->post('/debug-save', function (\Illuminate\Http\Request $request) {
    try {
        $focus = \App\Models\FocusSession::create([
            'user_id' => auth()->id(),
            'minutes' => 1,
            'type'    => 'test',
        ]);
        $task = \App\Models\Task::create([
            'user_id' => auth()->id(),
            'title'   => '__debug_test__',
            'type'    => 'general',
            'priority'=> 'low',
            'status'  => 'pending',
        ]);
        return response()->json([
            'auth_user_id'    => auth()->id(),
            'focus_saved_id'  => $focus->id,
            'task_saved_id'   => $task->id,
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// --- المسارات العامة (بدون تسجيل دخول) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');

// --- المسارات المحمية (تحتاج Token) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/user/update-profile', [AuthController::class, 'updateProfile']);

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']); 
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/tasks', [DashboardController::class, 'highPriorityTasks']);
        Route::get('/academic-summary', [DashboardController::class, 'academicSummary']);
    });

    // Focus Sessions
    Route::prefix('focus-sessions')->group(function () {
        Route::post('/', [FocusController::class, 'store']);
        Route::get('/stats', [FocusController::class, 'dailyStats']);
    });

    // Tasks (تم توحيدها هنا وحذف التكرار)
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']); // غيرتها لـ put لتناسب axios.put أحياناً
    Route::patch('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    // Semesters & Courses
    Route::apiResource('semesters', SemesterController::class);
    Route::apiResource('courses', CourseController::class);

    // Resources
    Route::apiResource('resources', ResourceController::class);

    // Exam Topics (المواضيع المرتبطة بامتحان في كورس معين)
    Route::get('/courses/{courseId}/tasks/{taskId}', [ExamTopicController::class, 'index']);
    Route::post('/courses/{courseId}/tasks/{taskId}', [ExamTopicController::class, 'store']);
    Route::put('/exam-topics/{id}', [ExamTopicController::class, 'update']);
    Route::patch('/exam-topics/{id}/toggle', [ExamTopicController::class, 'toggle']);
    Route::delete('/exam-topics/{id}', [ExamTopicController::class, 'destroy']);

    Route::apiResource('learning-plans', LearningController::class);
    Route::apiResource('reflections', ReflectionController::class);
    Route::put('/weekly-plans/{id}', [WeeklyPlanController::class, 'update']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});