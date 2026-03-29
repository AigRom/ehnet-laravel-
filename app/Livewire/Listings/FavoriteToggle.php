<?php

namespace App\Livewire\Listings;

use App\Models\Listing;
use Livewire\Component;

class FavoriteToggle extends Component
{
    public Listing $listing;
    public bool $isFavorited = false;

    public function mount(Listing $listing): void
    {
        $this->listing = $listing;

        if (auth()->check()) {
            $this->isFavorited = auth()->user()
                ->favorites()
                ->where('listing_id', $listing->id)
                ->exists();
        }
    }

    public function toggle(): void
    {
        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Lemmikuks lisamiseks logi sisse.');
            return;
        }

        $user = auth()->user();

        if ($this->isFavorited) {
            $user->favorites()->detach($this->listing->id);
            $this->isFavorited = false;
            return;
        }

        $user->favorites()->syncWithoutDetaching([$this->listing->id]);
        $this->isFavorited = true;
    }

    public function render()
    {
        return view('livewire.listings.favorite-toggle');
    }
}