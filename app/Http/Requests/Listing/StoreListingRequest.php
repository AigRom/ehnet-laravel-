<?php

namespace App\Http\Requests\Listing;

use Illuminate\Validation\Validator;

class StoreListingRequest extends ListingFormRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'submission_token' => ['required', 'string'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $sessionToken = session('listing_submission_token');
            $formToken = $this->input('submission_token');

            if (! $sessionToken || ! $formToken || ! hash_equals((string) $sessionToken, (string) $formToken)) {
                $validator->errors()->add(
                    'title',
                    'Vormi topeltsaatmine blokeeriti. Palun proovi uuesti.'
                );
            }

            if ($this->isDraft() && ! $this->hasAnyDraftContent('images')) {
                $validator->errors()->add(
                    'title',
                    'Täiesti tühja mustandit ei salvestata. Lisa vähemalt pealkiri, kategooria, asukoht, hind, seisukord, kättesaamine või pilt.'
                );
            }
        });
    }

    public function normalizedImagesOrder(int $fileCount): array
    {
        $order = array_values(array_filter(array_map(
            fn ($value) => is_numeric($value) ? (int) $value : null,
            $this->imagesOrder()
        ), fn ($value) => $value !== null));

        if (count($order) !== $fileCount) {
            return range(0, max(0, $fileCount - 1));
        }

        return $order;
    }
}