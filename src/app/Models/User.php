<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    // Roleとのリレーション
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // 勤怠データとのリレーション
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // 勤怠修正申請とのリレーション
    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }

    // 休憩データとのリレーション
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }
}