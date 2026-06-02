<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FocusSession extends Model
{
    use HasFactory;

    // هذا السطر هو الأهم، بدونه ستظل الداتا فارغة دائماً
    protected $fillable = [
        'user_id',
        'task_id',
        'minutes',
        'type'
    ];
}