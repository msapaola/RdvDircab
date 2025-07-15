<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AppointmentUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'assistant']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $today = Carbon::today();
        $maxDate = Carbon::today()->addMonths(6); // 6 mois maximum pour les admins

        return [
            'admin_notes' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-ZÀ-ÿ0-9\s\-\.,!?\(\)\n\r]+$/', // Texte sécurisé
            ],
            'preferred_date' => [
                'required',
                'date',
                'after_or_equal:' . $today->format('Y-m-d'),
                'before_or_equal:' . $maxDate->format('Y-m-d'),
            ],
            'preferred_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $time = Carbon::createFromFormat('H:i', $value);
                    $startTime = Carbon::createFromFormat('H:i', '08:00');
                    $endTime = Carbon::createFromFormat('H:i', '17:00');
                    
                    if ($time < $startTime || $time > $endTime) {
                        $fail('L\'heure doit être entre 08:00 et 17:00.');
                    }
                },
            ],
            'rejection_reason' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[a-zA-ZÀ-ÿ0-9\s\-\.,!?\(\)]+$/', // Texte sécurisé
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'admin_notes.max' => 'Les notes administratives ne peuvent pas dépasser 1000 caractères.',
            'admin_notes.regex' => 'Les notes contiennent des caractères non autorisés.',
            'preferred_date.required' => 'La date est obligatoire.',
            'preferred_date.after_or_equal' => 'La date doit être aujourd\'hui ou dans le futur.',
            'preferred_date.before_or_equal' => 'La date ne peut pas être plus de 6 mois à l\'avance.',
            'preferred_time.required' => 'L\'heure est obligatoire.',
            'preferred_time.date_format' => 'Veuillez fournir une heure valide (HH:MM).',
            'rejection_reason.max' => 'La raison du refus ne peut pas dépasser 500 caractères.',
            'rejection_reason.regex' => 'La raison du refus contient des caractères non autorisés.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'admin_notes' => 'notes administratives',
            'preferred_date' => 'date',
            'preferred_time' => 'heure',
            'rejection_reason' => 'raison du refus',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer les données avant validation
        $this->merge([
            'admin_notes' => $this->admin_notes ? trim(strip_tags($this->admin_notes)) : null,
            'rejection_reason' => $this->rejection_reason ? trim(strip_tags($this->rejection_reason)) : null,
        ]);
    }
}
