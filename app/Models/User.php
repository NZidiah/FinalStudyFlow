<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
'name',
    'email',
    'password',
    'current_gpa',
    'completed_credit_hours',
    'total_credit_hours',
    'academic_year', // أضيفي هذا
    'university',    
    'major',
    'reminder_preferences',
    'language',
    'theme',
    'current_semester',
    'onboarding_completed',
    'avatar_url',
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'current_gpa' => 'float',
            'completed_credit_hours' => 'integer',
            'total_credit_hours' => 'integer',
            'reminder_preferences' => 'array',
            'current_semester' => 'integer',
            'onboarding_completed' => 'boolean',
        ];
    }

    public function semesters() {
        return $this->hasMany(Semester::class);
    }

    public function courses() {
        return $this->hasMany(Course::class);
    }
public function focusSessions() {
    return $this->hasMany(FocusSession::class);
}
public function tasks()
{
    return $this->hasMany(Task::class);
}
}
