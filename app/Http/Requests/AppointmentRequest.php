<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Route publique
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $today = Carbon::today();
        $maxDate = Carbon::today()->addMonths(3); // 3 mois maximum à l'avance

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\']+$/', // Lettres, espaces, tirets, apostrophes
            ],
            'email' => [
                'required',
                'email:rfc,dns', // Validation stricte email
                'max:255',
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/', // Numéros, espaces, tirets, parenthèses, +
            ],
            'subject' => [
                'required',
                'string',
                'min:10',
                'max:500',
                'regex:/^[a-zA-ZÀ-ÿ0-9\s\-\.,!?\(\)]+$/', // Texte sécurisé
            ],
            'message' => [
                'nullable',
                'string',
                'max:2000',
                'regex:/^[a-zA-ZÀ-ÿ0-9\s\-\.,!?\(\)\n\r]+$/', // Texte avec retours à la ligne
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
            'priority' => [
                'required',
                Rule::in(['normal', 'urgent', 'official']),
            ],
            'attachments.*' => [
                'nullable',
                'file',
                'max:10240', // 10MB max
                'mimes:pdf,doc,docx,jpg,jpeg,png', // Types autorisés
            ],
            'attachments' => [
                'nullable',
                'array',
                'max:5', // Max 5 fichiers
            ],
            'g-recaptcha-response' => [
                'required',
                'recaptcha', // Si Google reCAPTCHA est configuré
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.regex' => 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'Veuillez fournir une adresse email valide.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'phone.regex' => 'Veuillez fournir un numéro de téléphone valide.',
            'subject.required' => 'L\'objet est obligatoire.',
            'subject.min' => 'L\'objet doit contenir au moins 10 caractères.',
            'subject.max' => 'L\'objet ne peut pas dépasser 500 caractères.',
            'subject.regex' => 'L\'objet contient des caractères non autorisés.',
            'message.max' => 'Le message ne peut pas dépasser 2000 caractères.',
            'message.regex' => 'Le message contient des caractères non autorisés.',
            'preferred_date.required' => 'La date souhaitée est obligatoire.',
            'preferred_date.after_or_equal' => 'La date doit être aujourd\'hui ou dans le futur.',
            'preferred_date.before_or_equal' => 'La date ne peut pas être plus de 3 mois à l\'avance.',
            'preferred_time.required' => 'L\'heure souhaitée est obligatoire.',
            'preferred_time.date_format' => 'Veuillez fournir une heure valide (HH:MM).',
            'priority.required' => 'La priorité est obligatoire.',
            'priority.in' => 'La priorité sélectionnée n\'est pas valide.',
            'attachments.*.file' => 'Le fichier joint n\'est pas valide.',
            'attachments.*.max' => 'Chaque fichier ne peut pas dépasser 10MB.',
            'attachments.*.mimes' => 'Seuls les fichiers PDF, DOC, DOCX, JPG, JPEG et PNG sont autorisés.',
            'attachments.max' => 'Vous ne pouvez joindre que 5 fichiers maximum.',
            'g-recaptcha-response.required' => 'Veuillez valider le reCAPTCHA.',
            'g-recaptcha-response.recaptcha' => 'La validation reCAPTCHA a échoué.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'email' => 'email',
            'phone' => 'téléphone',
            'subject' => 'objet',
            'message' => 'message',
            'preferred_date' => 'date souhaitée',
            'preferred_time' => 'heure souhaitée',
            'priority' => 'priorité',
            'attachments' => 'pièces jointes',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer les données avant validation
        $this->merge([
            'name' => trim(strip_tags($this->name)),
            'email' => strtolower(trim($this->email)),
            'phone' => trim($this->phone),
            'subject' => trim(strip_tags($this->subject)),
            'message' => $this->message ? trim(strip_tags($this->message)) : null,
        ]);
    }
}
