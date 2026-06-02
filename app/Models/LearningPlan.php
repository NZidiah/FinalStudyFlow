<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningPlan extends Model
{
    protected $fillable = [
        'user_id', 'title', 'goal', 'description', 
        'category', 'target_skill', 'start_date', 'end_date', 'status',
        'stages', 'milestones'
    ];

    protected $casts = [
        'stages' => 'array',
        'milestones' => 'array',
    ];

    // علاقة مع المستخدم
    public function user() {
        return $this->belongsTo(User::class);
    }

    // علاقة مع المصادر (متعددة الأشكال)
    public function resources() {
        return $this->morphMany(Resource::class, 'resourceable');
    }
}
