<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Semester extends Model
{
    use HasFactory;

protected $fillable = ['name', 'academic_year', 'num_of_weeks', 'status', 'user_id'];
// تحويل أنواع البيانات عند الجلب
    protected $casts = [
        'num_of_weeks' => 'integer',
        // ملاحظة: academic_year عادة يكون string مثل "2025/2026" فلا نحتاج لعمل cast له كـ integer
    ];

    // العلاقة مع User
    public function user() {
        return $this->belongsTo(User::class);
    }

    // العلاقة مع Courses
    public function courses() {
        return $this->hasMany(Course::class);
    }
}