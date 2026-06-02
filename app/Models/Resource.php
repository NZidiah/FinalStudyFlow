<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'resourceable_id',
        'resourceable_type',
        'title',
        'type',
        'url',
        'description',
    ];

    // علاقة متعددة الأشكال
    public function resourceable()
    {
        return $this->morphTo();
    }
}
