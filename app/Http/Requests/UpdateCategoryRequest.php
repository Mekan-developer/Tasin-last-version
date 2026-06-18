<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'order'      => 'nullable|integer|min:0',
            'views'      => 'nullable|integer|min:0',
            'show_price' => 'boolean',
            'is_active'  => 'boolean',
            'icon'       => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Kategoriýa ady hökmany.',
            'name.max'      => 'Kategoriýa ady 255 simvoldan uzyn bolmaly däldir.',
            'order.integer' => 'Tertip belgisi bütin san bolmaly.',
            'views.integer' => 'Görülen sany bütin san bolmaly.',
            'icon.image'    => 'Diňe surat faýlyny ýükläp bilersiňiz.',
            'icon.max'      => 'Suratyň ululygy 2 MB-dan köp bolmaly däldir.',
        ];
    }
}
