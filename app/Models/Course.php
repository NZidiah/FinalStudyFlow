<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'semester_id',
        'title',
        'code',           // جديد: كود المادة
        'instructor',     // جديد: اسم المدرس
        'credits',
        'duration_weeks', // جديد: مدة الأسابيع
        'description',    // جديد: الوصف والملاحظات
        'image_url',      // جديد: رابط الصورة
        'numeric_grade',
        'status',         // planned / current / completed
    ];

    protected $casts = [
        'credits' => 'integer',
        'duration_weeks' => 'integer', // جديد: تحويل المدة لرقم
        'numeric_grade' => 'float',
    ];

    // العلاقة مع User
    public function user() {
        return $this->belongsTo(User::class);
    }

    // العلاقة مع Semester
    public function semester() {
        return $this->belongsTo(Semester::class);
    }

    // العلاقة مع المصادر (متعددة الأشكال)
    public function resources() {
        return $this->morphMany(Resource::class, 'resourceable');
    }

    // العلاقة مع المهام
    public function tasks() {
        return $this->hasMany(Task::class);
    }

    // الخطة الأسبوعية
    public function weeklyPlans() {
        return $this->hasMany(WeeklyPlan::class);
    }

    // Accessor لتجميع المهام حسب الأسبوع
    /*
    public function getWeeklyPlansAttribute() {
        return $this->tasks->groupBy('week_number');
    }
    */
}