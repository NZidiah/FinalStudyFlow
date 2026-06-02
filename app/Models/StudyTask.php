<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyTask extends Model
{
    protected $fillable = ['weekly_plan_id', 'title', 'completed'];
}