<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUtilizadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'telemovel'             => $this->telemovel ?: null,
            'role'                  => $this->role ?: null,
            'password'              => $this->password ?: null,
            'password_confirmation' => $this->password_confirmation ?: null,
        ]);
    }

    public function rules(): array
    {
        $utilizadorId = $this->route('utilizador')?->id;

        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($utilizadorId)],
            'telemovel' => ['nullable', 'string', 'max:20'],
            'password'  => ['nullable', 'string', 'confirmed', Password::defaults()],
            'role'      => ['nullable', 'string', 'exists:roles,name'],
            'estado'    => ['required', 'in:ativo,inativo'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'O nome é obrigatório.',
            'email.required' => 'O email é obrigatório.',
            'email.unique'   => 'Este email já está em uso.',
        ];
    }
}
