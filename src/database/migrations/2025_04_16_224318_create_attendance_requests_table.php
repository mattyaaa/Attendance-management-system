<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // 修正申請を行ったユーザー
            $table->foreignId('attendance_id')->constrained('attendances'); // 対象の勤怠データ
            $table->date('date'); // 勤怠日
            $table->time('time_in')->nullable(); // 修正後の出勤時間
            $table->time('time_out')->nullable(); // 修正後の退勤時間
            $table->text('remarks')->nullable(); // 備考
            $table->enum('status', ['not_requested', 'pending', 'approved'])->default('not_requested'); // 修正申請のステータス
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_requests');
    }
}
