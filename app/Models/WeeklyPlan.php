<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyPlan extends Model
{
    protected $fillable = ['course_id', 'week_number', 'title', 'completed'];

    public function studyTasks()
    {
        return $this->hasMany(StudyTask::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    protected $casts = [
        'completed' => 'boolean',
    ];
}
