<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id','attendance_request_id','break_in', 'break_out'
    ];


    // 勤怠データとのリレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
    
    public function attendanceRequest()
{
    return $this->belongsTo(AttendanceRequest::class, 'attendance_request_id');
}
}
