<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['weekly_plan_id', 'title', 'due_date', 'status'];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function weeklyPlan()
    {
        return $this->belongsTo(WeeklyPlan::class);
    }
}
