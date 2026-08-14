<?php

namespace App\Http\Requests;

use App\Models\Devise;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviseRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
          /** @var Devise|null $devise */
        $devise = $this->route('devise');
        return [
            //
             'code' => [
                'sometimes',
                'string',
                'size:3',
               // 'regex:/^[A-Z]{3}$/',//ici j'acceptais uniquement les miniscules
                'regex:/^[a-zA-Z]{3}$/',
                Rule::unique('devises', 'code')
                    ->ignore($devise?->id),
            ],

            'nom' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'symbole' => [
                'sometimes',
                'string',
                'max:10',
            ],

            'precision' => [
                'sometimes',
                'integer',
                'min:0',
                'max:6',
            ],

            'position_symbole' => [
                'sometimes',
                'string',
                // 'max:10',
                Rule::in(['avant', 'apres']),
            ],

            'actif' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
{
    return [
           'code.string' => 'Le code de la devise doit être une chaîne de caractères.',
            'code.size' => 'Le code de la devise doit contenir exactement 3 caractères.',
            // 'code.regex' => 'Le code de la devise doit contenir exactement 3 lettres majuscules.',
            'code.regex' => 'Le code de la devise doit contenir exactement 3 lettres.',
            'code.unique' => 'Ce code de devise est déjà utilisé.',

            'nom.string' => 'Le nom de la devise doit être une chaîne de caractères.',
            'nom.max' => 'Le nom de la devise ne doit pas dépasser 255 caractères.',

            'symbole.string' => 'Le symbole de la devise doit être une chaîne de caractères.',
            'symbole.max' => 'Le symbole de la devise ne doit pas dépasser 10 caractères.',

            'precision.integer' => 'La précision doit être un nombre entier.',
            'precision.min' => 'La précision doit être supérieure ou égale à 0.',
            'precision.max' => 'La précision ne doit pas dépasser 6.',

            'position_symbole.string' => 'La position du symbole doit être une chaîne de caractères.',
            'position_symbole.in' => 'La position du symbole doit être "avant" ou "apres".',

            'actif.boolean' => 'Le champ actif doit être vrai ou faux.',
    ];
}

// passe le code en MAJUSCULE avant  l'execution des regles validation
protected function prepareForValidation(): void
{
    if ($this->has('code')) {
        $this->merge([
            'code' => strtoupper($this->string('code')->trim()->toString()),
        ]);
    }
}
}
