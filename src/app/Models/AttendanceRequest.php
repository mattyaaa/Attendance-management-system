<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'attendance_id',
        'name',
        'date',
        'time_in',
        'time_out',
        'break_in_1',
        'break_out_1',
        'break_in_2',
        'break_out_2',
        'remarks',
    ];

    // Userとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Attendanceとのリレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 修正申請の結果とのリレーション
    public function result()
    {
        return $this->hasOne(AttendanceRequestResult::class);
    }
}
