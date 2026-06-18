<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:admin_users,email',
            'phone'                 => 'nullable|string|max:50',
            'role'                  => 'required|in:admin,manager',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'At hökmany.',
            'name.max'          => 'At 255 simvoldan uzyn bolmaly däldir.',
            'email.required'    => 'E-poçta salgysy hökmany.',
            'email.email'       => 'Dogry e-poçta salgysyny giriziň.',
            'email.unique'      => 'Bu e-poçta salgysy eýýäm ulanylýar.',
            'phone.max'         => 'Telefon belgisi 50 simvoldan uzyn bolmaly däldir.',
            'role.required'     => 'Rol saýlanmaly.',
            'role.in'           => 'Rol diňe admin ýa-da manager bolup biler.',
            'password.required' => 'Parol hökmany.',
            'password.min'      => 'Parol iň az 8 simvoldan ybarat bolmaly.',
            'password.confirmed' => 'Parollar gabat gelmeýär.',
            'password_confirmation.required' => 'Paroly tassyklamak hökmany.',
        ];
    }
}
