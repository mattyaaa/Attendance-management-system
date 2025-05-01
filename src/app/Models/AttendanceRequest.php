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
        'date',
        'time_in',
        'time_out',
        'remarks',
        'status',
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

    // BreakTimeとのリレーション
    public function breaks()
    {
    return $this->hasMany(BreakTime::class, 'attendance_request_id');
    }

    }

