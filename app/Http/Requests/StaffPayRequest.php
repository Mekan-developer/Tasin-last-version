<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffPayRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'type'   => 'required|in:salary,advance,debt_pay',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Mukdar hökmany.',
            'amount.numeric'  => 'Mukdar san bolmaly.',
            'amount.min'      => 'Mukdar 0-dan uly bolmaly.',
            'type.required'   => 'Töleg görnüşi saýlanmaly.',
            'type.in'         => 'Töleg görnüşi nädogry.',
        ];
    }
}
