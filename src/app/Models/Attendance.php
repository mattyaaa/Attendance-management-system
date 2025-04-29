<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'time_in',
        'time_out',
        'status',
    ];

    // Userとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 勤怠修正申請とのリレーション
    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }
    // 休憩時間とのリレーション
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }
}
