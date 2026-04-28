<?php

namespace App\Services\Listing;

use App\Models\User;

class ListingPriceService
{
    public function payloadFor(User $user, array $validated): array
    {
        $priceMode = (string) ($validated['price_mode'] ?? 'deal');

        $price = match ($priceMode) {
            'free' => '0',
            'price' => $validated['price'] ?? null,
            default => null,
        };

        $vatIncluded = $this->isBusinessAccount($user)
            && $priceMode === 'price'
            && (bool) ($validated['vat_included'] ?? false);

        return [
            'price' => $price,
            'vat_included' => $vatIncluded,
        ];
    }

    public function isBusinessAccount(User $user): bool
    {
        return $user->type === 'business' || filled($user->company_name);
    }
}