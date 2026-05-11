<?php

namespace App\Services\Statistics;

use App\Models\Listing;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PlatformStatisticsService
{
    /**
     * MVP / marketinguline CO₂ hinnang.
     *
     * Praegu EHNET-is ei ole kuulutustel veel kogust, kaalu ega materjalipõhist CO₂ faktorit.
     * Seetõttu kasutame ajutiselt lihtsat keskmist:
     *
     * 1 lõpetatud tehing = 25 kg hinnangulist CO₂ säästu.
     *
     * See EI OLE täpne teaduslik arvutus.
     * Tulevikus tuleb see asendada täpsema loogikaga:
     * kogus × hinnanguline kaal × materjalipõhine CO₂ faktor.
     */
    private const AVERAGE_CO2_KG_PER_COMPLETED_TRADE = 25;

    public function publicSummary(): array
    {
        return Cache::remember('statistics.public-summary', now()->addMinutes(10), function () {
            $usersCount = User::query()->count();

            $listingsCount = Listing::query()
                ->homeFeed()
                ->count();

            $savedCo2Kg = $this->calculateSavedCo2Kg();

            return [
                'usersCount' => $usersCount,
                'listingsCount' => $listingsCount,

                // Toores väärtus kg-des, kui seda on hiljem vaja admin vaates või graafikutes.
                'savedCo2Kg' => $savedCo2Kg,

                // Vormindatud väärtus avalehe headeri jaoks, näiteks "250 kg" või "1,2 t".
                'savedCo2' => $this->formatCo2($savedCo2Kg),
            ];
        });
    }

    private function calculateSavedCo2Kg(): float
    {
        /**
         * MVP loogika:
         * loeme ainult päriselt lõpetatud tehinguid.
         *
         * Trade::STATUS_COMPLETED tähendab, et tehing on lõpule jõudnud.
         * completed_at kontroll annab lisakindluse, et tehing on päriselt lõpetamise aja saanud.
         */
        $completedTradesCount = Trade::query()
            ->where('status', Trade::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->count();

        return $completedTradesCount * self::AVERAGE_CO2_KG_PER_COMPLETED_TRADE;
    }

    private function formatCo2(float $kg): string
    {
        if ($kg >= 1000) {
            return number_format($kg / 1000, 1, ',', ' ') . ' t';
        }

        return number_format($kg, 0, ',', ' ') . ' kg';
    }
}