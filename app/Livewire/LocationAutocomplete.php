<?php

namespace App\Livewire;

use App\Models\Location;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class LocationAutocomplete extends Component
{
    public string $search = '';

    // Livewire parent binding
    #[Modelable]
    public ?int $location_id = null;

    // Klassikalise vormi jaoks
    public ?int $selectedId = null;

    // Hidden input name
    public string $name = 'location_id';

    /** @var array<int, array{id:int,label:string}> */
    public array $results = [];

    protected $listeners = [
        'loc:set' => 'setLocation',
        'loc:clear' => 'clearSelection',
    ];

    public function mount(?int $selectedId = null, ?string $name = 'location_id', ?int $initialId = null): void
    {
        $this->name = $name ?: 'location_id';

        // toeta nii vana initialId kui uut selectedId
        $id = $this->location_id ?: $selectedId ?: $initialId;

        if ($id) {
            $this->applyLocationId((int) $id);
        }
    }

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

        $this->dispatch('loc:selected',
            id: (int) $this->selectedId,
            label: (string) $this->search
        );
    }

    public function setLocation(int $id): void
    {
        $this->applyLocationId($id);

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