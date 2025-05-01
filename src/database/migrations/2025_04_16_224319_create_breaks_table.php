<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade'); // 外部キー制約
            $table->foreignId('attendance_request_id')->nullable()->constrained('attendance_requests')->onDelete('cascade');// 修正申請の外部キー
            $table->time('break_in')->nullable(); // 休憩開始
            $table->time('break_out')->nullable(); // 休憩終了
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 外部キー制約を削除
        Schema::table('breaks', function (Blueprint $table) {
        if (Schema::hasColumn('breaks', 'attendance_id')) {
            $table->dropForeign(['attendance_id']); // 外部キー削除
        }
        if (Schema::hasColumn('breaks', 'attendance_request_id')) {
            $table->dropForeign(['attendance_request_id']); // 外部キー削除
        }
        });
        Schema::dropIfExists('breaks');
    }
};