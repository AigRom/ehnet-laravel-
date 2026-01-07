<?php

namespace App\Livewire;

use App\Models\Location;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class LocationAutocomplete extends Component
{
    public string $search = '';

    // Volt / Livewire: wire:model="location_id"
    #[Modelable]
    public ?int $location_id = null;

    // Klassikaline POST vorm: hidden input name="location_id"
    public ?int $selectedId = null;

    /** @var array<int, array{id:int,label:string}> */
    public array $results = [];

    protected $listeners = [
        'loc:set' => 'setLocation',
        'loc:clear' => 'clearSelection',
    ];

    public function mount(?int $initialId = null): void
    {
        // Prioriteet: wire:model -> initialId
        $id = $this->location_id ?: $initialId;

        if ($id) {
            $this->applyLocationId((int) $id);
        }
    }

    /**
     * Livewire 3: updatedSearch($value) on stabiilne
     */
    public function updatedSearch($value): void
    {
        $term = mb_strtolower(trim((string) $value));

        // kui kirjutatakse uuesti, tühistame valiku
        $this->selectedId = null;
        $this->location_id = null;

        if (mb_strlen($term) < 2) {
            $this->results = [];
            return;
        }

        // Prefix match:
        // 1) name_et LIKE 'al%'
        // 2) full_label_et LIKE 'al%'
        // 3) full_label_et LIKE '%, al%'
        $namePrefix = "{$term}%";
        $labelPrefixStart = "{$term}%";
        $labelPrefixAfterComma = "%, {$term}%";

        $rows = Location::query()
            ->where('is_valid', 1)
            ->where(function ($q) use ($namePrefix, $labelPrefixStart, $labelPrefixAfterComma) {
                $q->whereRaw('LOWER(name_et) LIKE ?', [$namePrefix])
                  ->orWhereRaw('LOWER(full_label_et) LIKE ?', [$labelPrefixStart])
                  ->orWhereRaw('LOWER(full_label_et) LIKE ?', [$labelPrefixAfterComma]);
            })
            ->orderByRaw(
                "CASE
                    WHEN LOWER(name_et) LIKE ? THEN 0
                    WHEN LOWER(full_label_et) LIKE ? THEN 1
                    WHEN LOWER(full_label_et) LIKE ? THEN 2
                    ELSE 3
                 END",
                [$namePrefix, $labelPrefixStart, $labelPrefixAfterComma]
            )
            ->orderBy('full_label_et')
            ->limit(10)
            ->get(['id', 'full_label_et']);

        $this->results = $rows->map(fn ($loc) => [
            'id' => (int) $loc->id,
            'label' => $this->reverseLabel((string) ($loc->full_label_et ?? '')),
        ])->toArray();
    }

    public function selectLocation(int $id): void
    {
        $this->applyLocationId($id);

        // ✅ anna parentile märku (checkbox loogika + location_label täitmine)
        $this->dispatch('loc:selected',
            id: (int) $this->selectedId,
            label: (string) $this->search
        );
    }

    public function setLocation(int $id): void
    {
        $this->applyLocationId($id);

        // ✅ ka "Use my location" puhul saadame labeli (et location_label täituks)
        $this->dispatch('loc:selected',
            id: (int) $this->selectedId,
            label: (string) $this->search
        );
    }

    public function clearSelection(): void
    {
        $this->selectedId = null;
        $this->location_id = null;
        $this->search = '';
        $this->results = [];

        // (valikuline) kui tahad ka labelit nullida frontis:
        $this->dispatch('loc:selected', id: null, label: '');
    }

    private function applyLocationId(int $id): void
    {
        $loc = Location::find($id);
        if (! $loc) {
            return;
        }

        $this->selectedId = (int) $loc->id;
        $this->location_id = (int) $loc->id;

        $raw = (string) ($loc->full_label_et ?? '');
        $this->search = $this->reverseLabel($raw);

        $this->results = [];
    }

    private function reverseLabel(string $label): string
    {
        $parts = array_map('trim', explode(',', $label));
        $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));

        if (count($parts) <= 1) {
            return $label;
        }

        return implode(', ', array_reverse($parts));
    }

    public function render()
    {
        return view('livewire.location-autocomplete');
    }
}
