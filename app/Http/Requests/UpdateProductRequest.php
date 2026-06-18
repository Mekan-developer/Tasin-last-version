<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'category_id'       => 'required|exists:categories,id',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'currency'          => 'required|in:USD,TMT',
            'order'             => 'nullable|integer|min:0',
            'active_image'      => 'nullable|integer|min:0',
            'is_new'            => 'boolean',
            'is_active'         => 'boolean',
            'variants'            => 'nullable|array',
            'variants.*.name'     => 'required_with:variants|string|max:255',
            'variants.*.price'    => 'nullable|numeric|min:0',
            'variants.*.currency' => 'nullable|in:USD,TMT',
            'images_upload'       => 'nullable|array|max:6',
            'images_upload.*'     => 'image|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Haryt ady hökmany.',
            'name.max'             => 'Haryt ady 255 simvoldan uzyn bolmaly däldir.',
            'category_id.required' => 'Kategoriýa saýlanmaly.',
            'category_id.exists'   => 'Saýlanan kategoriýa tapylmady.',
            'price.required'       => 'Baha hökmany.',
            'price.numeric'        => 'Baha san bolmaly.',
            'price.min'            => 'Baha 0-dan kiçi bolmaly däldir.',
            'currency.required'    => 'Walýuta saýlanmaly.',
            'currency.in'          => 'Walýuta diňe USD ýa-da TMT bolup biler.',
            'order.integer'        => 'Tertip belgisi bütin san bolmaly.',
            'variants.*.name.required_with'     => 'Wariant ady hökmany.',
            'variants.*.name.max'               => 'Wariant ady 255 simvoldan uzyn bolmaly däldir.',
            'variants.*.price.required_with'    => 'Wariant bahasy hökmany.',
            'variants.*.price.numeric'          => 'Wariant bahasy san bolmaly.',
            'variants.*.price.min'              => 'Wariant bahasy 0-dan kiçi bolmaly däldir.',
            'variants.*.currency.required_with' => 'Wariant walýutasy saýlanmaly.',
            'variants.*.currency.in'            => 'Wariant walýutasy diňe USD ýa-da TMT bolup biler.',
            'images_upload.max'    => 'Iň köp 6 surat ýükläp bilersiňiz.',
            'images_upload.*.image' => 'Diňe surat faýllaryny ýükläp bilersiňiz.',
            'images_upload.*.max'   => 'Her suratyň ululygy 4 MB-dan köp bolmaly däldir.',
        ];
    }
}
