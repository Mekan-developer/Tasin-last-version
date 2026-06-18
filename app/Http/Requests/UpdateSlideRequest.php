<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSlideRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'badge'       => 'nullable|string|max:10',
            'bg_color'    => 'nullable|string|max:20',
            'price'       => 'nullable|numeric|min:0',
            'currency'    => 'nullable|in:USD,TMT',
            'old_price'   => 'nullable|numeric|min:0',
            'discount'    => 'nullable|integer|min:0|max:100',
            'order'       => 'nullable|integer|min:0',
            'image'       => 'nullable|image|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'    => 'Sergi sözbaşysy hökmany.',
            'title.max'         => 'Sözbaşy 255 simvoldan uzyn bolmaly däldir.',
            'badge.max'         => 'Bellik 10 simvoldan uzyn bolmaly däldir.',
            'bg_color.max'      => 'Reňk bahasy 20 simvoldan uzyn bolmaly däldir.',
            'price.numeric'     => 'Baha san bolmaly.',
            'price.min'         => 'Baha 0-dan kiçi bolmaly däldir.',
            'currency.in'       => 'Walýuta diňe USD ýa-da TMT bolup biler.',
            'old_price.numeric' => 'Köne baha san bolmaly.',
            'discount.integer'  => 'Arzanladyş bütin san bolmaly.',
            'discount.max'      => 'Arzanladyş 100-den köp bolmaly däldir.',
            'order.integer'     => 'Tertip belgisi bütin san bolmaly.',
            'image.image'       => 'Diňe surat faýlyny ýükläp bilersiňiz.',
            'image.max'         => 'Suratyň ululygy 5 MB-dan köp bolmaly däldir.',
        ];
    }
}
