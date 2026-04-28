<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ListingFormRequest extends FormRequest
{
    protected array $allowedDeliveryOptions = [
        'pickup',
        'seller_delivery',
        'courier',
        'agreement',
    ];

    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        $priceMode = (string) $this->input('price_mode', 'deal');
        $price = $this->input('price');

        if (is_string($price)) {
            $price = trim($price);

            // Eemaldame tavalised tühikud ja mittekatkevad tühikud
            $price = str_replace([' ', "\xc2\xa0", "\u{00A0}"], '', $price);

            // Lubame komaga hinna: 200,7 -> 200.7
            $price = str_replace(',', '.', $price);
        }

        if ($priceMode === 'deal') {
            $price = null;
        }

        if ($priceMode === 'free') {
            $price = '0';
        }

        $deliveryOptions = $this->input('delivery_options', []);

        if (! is_array($deliveryOptions)) {
            $deliveryOptions = [];
        }

        $deliveryOptions = array_values(array_unique(array_filter(
            $deliveryOptions,
            fn ($value) => is_string($value) && in_array($value, $this->allowedDeliveryOptions, true)
        )));

        $this->merge([
            'price' => $price,
            'price_mode' => $priceMode,
            'vat_included' => $this->boolean('vat_included'),
            'delivery_options' => $deliveryOptions,
        ]);
    }

    public function formAction(): string
    {
        $action = (string) $this->input('action', 'publish');

        return in_array($action, ['publish', 'draft'], true)
            ? $action
            : 'publish';
    }

    public function isDraft(): bool
    {
        return $this->formAction() === 'draft';
    }

    public function deliveryOptions(): array
    {
        $validated = $this->validated();

        return array_values(array_unique($validated['delivery_options'] ?? []));
    }

    public function imagesOrder(): array
    {
        return $this->safeJsonArray($this->input('images_order'));
    }

    protected function baseRules(): array
    {
        $isDraft = $this->isDraft();

        return [
            'action' => ['nullable', Rule::in(['publish', 'draft'])],
            'title' => [$isDraft ? 'nullable' : 'required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => [$isDraft ? 'nullable' : 'required', 'exists:categories,id'],
            'location_id' => [$isDraft ? 'nullable' : 'required', 'exists:locations,id'],

            'price_mode' => ['nullable', Rule::in(['deal', 'free', 'price'])],
            'price' => $this->priceRules(),
            'vat_included' => ['nullable', 'boolean'],

            'condition' => ['nullable', Rule::in(['new', 'used', 'leftover'])],
            'delivery_options' => ['nullable', 'array', 'max:4'],
            'delivery_options.*' => [Rule::in($this->allowedDeliveryOptions)],
            'images_order' => ['nullable', 'string'],
        ];
    }

    protected function priceRules(): array
    {
        $mode = (string) $this->input('price_mode', 'deal');

        $rules = ['nullable', 'numeric', 'max:999999.99'];

        if ($mode === 'price') {
            if (! $this->isDraft()) {
                array_unshift($rules, 'required');
            }

            $rules[] = 'min:0.01';

            return $rules;
        }

        $rules[] = 'min:0';

        return $rules;
    }

    protected function hasAnyDraftContent(string $imageField): bool
    {
        $priceMode = (string) $this->input('price_mode', 'deal');

        return filled($this->input('title'))
            || filled($this->input('description'))
            || filled($this->input('category_id'))
            || filled($this->input('location_id'))
            || filled($this->input('condition'))
            || ! empty($this->input('delivery_options', []))
            || $priceMode === 'free'
            || ($priceMode === 'price' && filled($this->input('price')))
            || ! empty($this->file($imageField, []));
    }

    protected function safeJsonArray(mixed $raw): array
    {
        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}