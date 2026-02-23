<?php

namespace App\Livewire\Listings;

use Livewire\Component;
use App\Models\Listing;

class FavoriteToggle extends Component
{
    public Listing $listing;
    public bool $isFavorited = false;

    public function mount(Listing $listing)
    {
        $this->listing = $listing;

        if (auth()->check()) {
            $this->isFavorited = auth()->user()
                ->favorites()
                ->where('listing_id', $listing->id)
                ->exists();
        }
    }

    public function toggle()
    {
        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Lemmikuks lisamiseks logi sisse.');
            return;
        }

        $user = auth()->user();

        if ($this->isFavorited) {
            $user->favorites()->detach($this->listing->id);
            $this->isFavorited = false;
        } else {
            $user->favorites()->attach($this->listing->id);
            $this->isFavorited = true;
        }
    }

    public function render()
    {
        return view('livewire.listings.favorite-toggle');
    }
}
