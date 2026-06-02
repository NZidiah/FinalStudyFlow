<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

 protected $fillable = [
    'user_id', 'course_id', 'week_number', 'title', 'description', 'type', 
    'priority', 'status', 'due_date', 'due_time', 
    'reminder', 'reminder_value', 'reminder_unit',
    'is_recurring', 'repeat_frequency', 'repeat_interval'
];

       protected $casts = [
        'reminder' => 'boolean',
        'due_date' => 'date',
    ];

        public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }


}