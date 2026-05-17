<?php

namespace App\Http\Requests;

use App\Models\SupportThread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->filled('name') ? trim((string) $this->input('name')) : null,
            'email' => $this->filled('email') ? trim((string) $this->input('email')) : null,
            'subject' => $this->filled('subject') ? trim((string) $this->input('subject')) : null,
            'message' => $this->filled('message') ? trim((string) $this->input('message')) : null,
            'website' => $this->filled('website') ? trim((string) $this->input('website')) : null,
        ]);
    }

    public function rules(): array
    {
        $isGuest = ! $this->user();

        return [
            'name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'email' => [
                $isGuest ? 'required' : 'nullable',
                'email',
                'max:255',
            ],

            'category' => [
                'required',
                'string',
                Rule::in([
                    SupportThread::CATEGORY_PROBLEM,
                    SupportThread::CATEGORY_LISTING,
                    SupportThread::CATEGORY_ACCOUNT,
                    SupportThread::CATEGORY_FEEDBACK,
                    SupportThread::CATEGORY_SUGGESTION,
                    SupportThread::CATEGORY_GENERAL,
                ]),
            ],

            'subject' => [
                'nullable',
                'string',
                'max:150',
            ],

            'message' => [
                'required',
                'string',
                'min:10',
                'max:3000',
            ],

            'website' => [
                'nullable',
                'size:0',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nimi',
            'email' => 'e-post',
            'category' => 'teema',
            'subject' => 'pealkiri',
            'message' => 'sõnum',
            'website' => 'veebileht',
        ];
    }
}