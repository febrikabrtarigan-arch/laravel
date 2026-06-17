<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 
            \Illuminate\Validation\Rule::unique('users', 'email')->whereNull('deleted_at')],
            'nip'      => ['required', 'string', 'max:50', 
             \Illuminate\Validation\Rule::unique('users', 'nip')->whereNull('deleted_at')],
            'password' => ['required', 'confirmed', 'min:8'],
            'jabatan'  => ['required', 'string', 'max:255'],
            'no_hp'    => ['required', 'string', 'max:20'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name'     => 'Nama Lengkap',
            'nip'      => 'NIP',
            'email'    => 'Alamat Email',
            'password' => 'Kata Sandi',
            'jabatan'  => 'Jabatan',
            'no_hp'    => 'Nomor HP',
        ];
    }
}
