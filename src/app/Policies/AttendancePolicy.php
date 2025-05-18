<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;

class AttendancePolicy
{
    /**
     * Determine whether the user can update the attendance.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Attendance  $attendance
     * @return bool
     */
    public function update(User $user, Attendance $attendance)
    {
        // 管理者のみ操作可能
        return $user->role_id === 2; // role_id === 2 は管理者
    }
}