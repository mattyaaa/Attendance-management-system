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
            $table->unsignedBigInteger('attendance_id'); // カラム定義
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade'); // 外部キー制約
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
        Schema::dropIfExists('breaks');
    }
};