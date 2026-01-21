<?php

namespace App\Services;

use App\LuckyDraw;
use App\LuckyDrawEntry;
use App\Models\CustomerScheme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LuckyDrawService
{
    /**
     * Add customer to lucky draw when they complete a cycle
     */
    public function addEntryOnCycleCompletion(CustomerScheme $customerScheme, int $cycleNumber): bool
    {
// dd($customerScheme, $cycleNumber);
        // Get active lucky draw
        $activeDraw = LuckyDraw::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            // ->whereNull('draw_date')
            ->first();
// dd($activeDraw);
        if (!$activeDraw) {
            Log::info('No active lucky draw found for cycle completion');
            return false;
        }
// dd('here');
        // Check if customer can enter this draw
        if (!$activeDraw->canCustomerEnter($customerScheme->customer_id)) {
            // dd('entries reached');
            Log::info("Customer {$customerScheme->customer_id} has reached maximum entries for draw {$activeDraw->id}");
            return false;
        }
// dd($activeDraw);
        try {
            DB::transaction(function () use ($activeDraw, $customerScheme, $cycleNumber) {
                LuckyDrawEntry::create([
                    'lucky_draw_id' => $activeDraw->id,
                    'customer_id' => $customerScheme->customer_id,
                    'customer_scheme_id' => $customerScheme->id,
                    'cycle_number' => $cycleNumber,
                    'entry_source' => 'cycle_completion'
                ]);

                Log::info("Added lucky draw entry for customer {$customerScheme->customer_id} on cycle {$cycleNumber}");
            });

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to add lucky draw entry: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active lucky draw with statistics
     */
    public function getActiveDraw(): ?LuckyDraw
    {
        return LuckyDraw::withCount('entries')
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereNull('draw_date')
            ->first();
    }

    /**
     * Conduct lucky draw and select winners
     */
    public function conductDraw(LuckyDraw $luckyDraw, array $prizes): array
    {
        $winners = [];

        DB::transaction(function () use ($luckyDraw, $prizes, &$winners) {
            // Get all entries for this draw
            $entries = $luckyDraw->entries()->inRandomOrder()->get();

            $winnerCount = 0;

            foreach ($prizes as $prizeIndex => $prize) {
                if ($winnerCount >= count($entries)) break;

                $winnerEntry = $entries[$winnerCount];

                $winnerEntry->markAsWinner(
                    $prize['type'],
                    $prize['amount'] ?? null
                );

                $winners[] = [
                    'entry' => $winnerEntry,
                    'prize' => $prize
                ];

                $winnerCount++;
            }

            // Update draw status
            $luckyDraw->update([
                'status' => 'completed',
                'draw_date' => now(),
                'prizes' => $prizes
            ]);
        });

        return $winners;
    }

    /**
     * Get customer's lucky draw history
     */
    public function getCustomerHistory($customerId)
    {
        return LuckyDrawEntry::with('luckyDraw')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('luckyDraw.title');
    }

    /**
     * Create new lucky draw
     */
    public function createDraw(array $data): LuckyDraw
    {
        return LuckyDraw::create($data);
    }
}
