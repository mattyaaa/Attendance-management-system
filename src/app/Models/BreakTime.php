<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'user_id',
        'date',
        'break_in_1',
        'break_out_1',
        'break_in_2',
        'break_out_2',
    ];

    // Userとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
