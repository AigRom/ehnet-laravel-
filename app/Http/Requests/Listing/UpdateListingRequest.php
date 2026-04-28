<?php

namespace App\Http\Requests\Listing;

use App\Models\Listing;
use Illuminate\Validation\Validator;

class UpdateListingRequest extends ListingFormRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        return $this->user()
            && $listing instanceof Listing
            && (int) $listing->user_id === (int) $this->user()->id;
    }

    public function formAction(): string
    {
        $listing = $this->route('listing');

        $default = $listing instanceof Listing && $listing->status === 'draft'
            ? 'draft'
            : 'publish';

        $action = (string) $this->input('action', $default);

        return in_array($action, ['publish', 'draft'], true)
            ? $action
            : $default;
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'new_images' => ['nullable', 'array', 'max:10'],
            'new_images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'deleted_image_ids' => ['nullable', 'string'],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->isDraft() && ! $this->hasAnyDraftContent('new_images')) {
                $validator->errors()->add(
                    'title',
                    'Täiesti tühja mustandit ei salvestata. Lisa vähemalt pealkiri, kategooria, asukoht, hind, seisukord, kättesaamine või pilt.'
                );
            }
        });
    }

    public function deletedImageIds(): array
    {
        return array_values(array_filter(array_map(
            fn ($value) => is_numeric($value) ? (int) $value : null,
            $this->safeJsonArray($this->input('deleted_image_ids'))
        ), fn ($value) => $value !== null && $value > 0));
    }
}