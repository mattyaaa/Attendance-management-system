<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceModificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i', 'after:time_in'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.break_in' => ['nullable', 'date_format:H:i', 'before_or_equal:time_out', 'after_or_equal:time_in'],
            'breaks.*.break_out' => ['nullable', 'date_format:H:i', 'before_or_equal:time_out', 'after_or_equal:breaks.*.break_in'],
            'remarks' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validation.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // 出勤・退勤時間のエラーメッセージ
            'time_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',

            // 休憩時間のエラーメッセージ
            'breaks.*.break_in.before_or_equal' => '休憩時間が勤務時間外です。',
            'breaks.*.break_in.after_or_equal' => '休憩時間が勤務時間外です。',
            'breaks.*.break_out.before_or_equal' => '休憩時間が勤務時間外です。',

            // 備考欄のエラーメッセージ
            'remarks.required' => '備考を記入してください。',
        ];
    }
}
