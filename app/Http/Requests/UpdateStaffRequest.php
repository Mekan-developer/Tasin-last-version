<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'position'   => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'salary'     => 'required|numeric|min:0',
            'paid'       => 'nullable|numeric|min:0',
            'debt'       => 'nullable|numeric|min:0',
            'hired_at'   => 'nullable|date',
            'is_active'  => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Işgäriň ady hökmany.',
            'name.max'         => 'At 255 simvoldan uzyn bolmaly däldir.',
            'position.max'     => 'Wezipe 255 simvoldan uzyn bolmaly däldir.',
            'department.max'   => 'Bölüm 255 simvoldan uzyn bolmaly däldir.',
            'salary.required'  => 'Aýlyk hökmany.',
            'salary.numeric'   => 'Aýlyk san bolmaly.',
            'salary.min'       => 'Aýlyk 0-dan kiçi bolmaly däldir.',
            'paid.numeric'     => 'Tölenen mukdar san bolmaly.',
            'debt.numeric'     => 'Bergi mukdary san bolmaly.',
            'hired_at.date'    => 'Işe alnan senesi dogry görnüşde bolmaly.',
        ];
    }
}
