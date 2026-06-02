<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamTopic extends Model
{
    protected $fillable = ['task_id', 'title', 'completed'];
}
