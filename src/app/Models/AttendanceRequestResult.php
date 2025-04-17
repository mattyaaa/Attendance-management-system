<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_request_id',
        'admin_user_id',
        'result_status',
    ];

    // 修正申請とのリレーション
    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }

    // 管理者Userとのリレーション
    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
