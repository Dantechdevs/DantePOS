<?php

namespace App\Http\Controllers;

use App\LuckyDraw;
use App\LuckyDrawEntry;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LuckyDrawController extends Controller
{
    public function index()
    {
        $luckyDraws = LuckyDraw::withCount(['entries', 'winners'])->get();
        return view('lucky_draws.index', compact('luckyDraws'));
    }

    public function create()
    {
        // Check if there's already an active draw
        $activeDraw = LuckyDraw::active()->first();
        if ($activeDraw) {
            return redirect()->route('lucky-draws.index')
                ->with('error', 'There is already an active lucky draw. Please deactivate it before creating a new one.');
        }

        return view('lucky_draws.create');
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,completed,cancelled',
            'max_entries_per_customer' => 'required|integer|min:1|max:10',
            'draw_date' => 'nullable|date|after:start_date',
        ]);



        // Validate prizes
        $prizes = json_decode($request->prizes, true);
        if (empty($prizes)) {
            return response()->json([
                'success' => false,
                'message' => 'At least one prize is required.',
                'errors' => ['prizes' => ['At least one prize is required.']]
            ], 422);
        }

        // Check if there's already an active draw when trying to create active draw
        if ($validated['status'] === 'active') {
            $activeDraw = LuckyDraw::active()->first();
            if ($activeDraw) {
                return response()->json([
                    'success' => false,
                    'message' => 'There is already an active lucky draw. Please deactivate it before creating a new one.',
                    'errors' => ['status' => ['There is already an active lucky draw.']]
                ], 422);
            }
        }

        // Validate each prize
        foreach ($prizes as $index => $prize) {
            if (empty($prize['type']) || empty($prize['name'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'All prizes must have both type and name.',
                    'errors' => ['prizes' => ['All prizes must have both type and name.']]
                ], 422);
            }
        }

        try {
            // DB::beginTransaction();

            // If setting as active, deactivate all others
            if ($validated['status'] === 'active') {
                LuckyDraw::where('status', 'active')->update(['status' => 'completed']);
            }
// echo "<pre>"; print_r($prizes); "</pre>"; exit;
            // Create the lucky draw
            $luckyDraw = LuckyDraw::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => $validated['status'],
                'max_entries_per_customer' => $validated['max_entries_per_customer'],
                'prizes' => $prizes,
                'draw_date' => $validated['draw_date']
            ]);
// echo "<pre>"; print_r($luckyDraw); "</pre>"; exit;
            // DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lucky draw created successfully!'
                // 'data' => $luckyDraw
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getParticipants($id)
    {
        try {
            $luckyDraw = LuckyDraw::findOrFail($id);

            $participants = LuckyDrawEntry::where('lucky_draw_id', $id)
                ->with('customer')
                ->get()
                ->groupBy('customer_id')
                ->map(function ($entries) {
                    $customer = $entries->first()->customer;
                    $wonPrizes = $entries->where('is_winner', true)->pluck('prize_won')->filter()->toArray();

                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'entries' => $entries->count(),
                        'won_prizes' => $wonPrizes,
                        'is_winner' => !empty($wonPrizes),
                        'entry_ids' => $entries->pluck('id')
                    ];
                })->values();

            $totalEntries = $participants->sum('entries');

            // Calculate probability for each participant
            $participants = $participants->map(function ($participant) use ($totalEntries) {
                $participant['probability'] = $totalEntries > 0 ?
                    round(($participant['entries'] / $totalEntries) * 100, 2) : 0;
                return $participant;
            });

            return response()->json([
                'success' => true,
                'participants' => $participants,
                'total_entries' => $totalEntries,
                'lucky_draw' => $luckyDraw,
                'available_prizes' => $this->getAvailablePrizes($luckyDraw, $participants)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load participants.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function spinDraw(Request $request, $id)
    {
        try {
            $request->validate([
                'prize_type' => 'required|string'
            ]);

            $luckyDraw = LuckyDraw::findOrFail($id);
            $prizeType = $request->prize_type;

            // Check if prize exists
            $prize = collect($luckyDraw->prizes)->firstWhere('type', $prizeType);
            if (!$prize) {
                return response()->json([
                    'success' => false,
                    'message' => 'Prize type not found.'
                ], 404);
            }

            // Check if prize is already won
            $alreadyWon = LuckyDrawEntry::where('lucky_draw_id', $id)
                ->where('prize_won', $prizeType)
                ->where('is_winner', true)
                ->exists();

            if ($alreadyWon) {
                return response()->json([
                    'success' => false,
                    'message' => 'This prize has already been won!'
                ], 422);
            }

            DB::beginTransaction();

            // Get all non-winning entries for this lucky draw
            $entries = LuckyDrawEntry::where('lucky_draw_id', $id)
                ->where('is_winner', false)
                ->get();

            if ($entries->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No eligible participants found for this prize.'
                ], 422);
            }

            // Create weighted array (each entry represents one chance)
            $weightedEntries = [];
            foreach ($entries as $entry) {
                $weightedEntries[] = [
                    'entry_id' => $entry->id,
                    'customer_id' => $entry->customer_id,
                    'customer_name' => $entry->customer->name,
                    'customer_email' => $entry->customer->email
                ];
            }

            // Randomly select winner
            $winnerEntry = $weightedEntries[array_rand($weightedEntries)];

            // Mark as winner
            LuckyDrawEntry::where('id', $winnerEntry['entry_id'])->update([
                'prize_won' => $prizeType,
                'won_at' => now(),
                'is_winner' => true
            ]);

            // Get winner's total entries count
            $winnerEntriesCount = LuckyDrawEntry::where('lucky_draw_id', $id)
                ->where('customer_id', $winnerEntry['customer_id'])
                ->count();

            // Get remaining available prizes
            $participants = $this->getParticipantsData($id);
            $availablePrizes = $this->getAvailablePrizes($luckyDraw, $participants);

            DB::commit();

            return response()->json([
                'success' => true,
                'winner' => [
                    'name' => $winnerEntry['customer_name'],
                    'email' => $winnerEntry['customer_email'],
                    'entries' => $winnerEntriesCount,
                    'entry_id' => $winnerEntry['entry_id'],
                    'prize_won' => $prizeType,
                    'prize_name' => $prize['name']
                ],
                'available_prizes' => $availablePrizes,
                'total_participants' => $entries->groupBy('customer_id')->count(),
                'total_entries' => $entries->count()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to spin draw. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            DB::beginTransaction();

            $luckyDraw = LuckyDraw::findOrFail($id);

            if ($luckyDraw->status === 'active') {
                $luckyDraw->update(['status' => 'completed']);
                $message = 'Lucky draw deactivated successfully!';
            } else {
                // Deactivate all other active draws
                // LuckyDraw::where('status', 'active')->update(['status' => 'completed']);
                // $luckyDraw->update(['status' => 'active']);
                // $message = 'Lucky draw activated successfully!';
            }

            DB::commit();

            return redirect()->back()->with('success', $message);

            // return response()->json([
            //     'success' => true,
            //     'message' => $message,
            //     'data' => $luckyDraw
            // ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function luckyDrawList(Request $request)
    {
        try {
            $luckyDraws = LuckyDraw::withCount(['entries', 'winners'])->get();

            return response()->json([
                'success' => true,
                'data' => $luckyDraws
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load lucky draws.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getParticipantsData($drawId)
    {
        return LuckyDrawEntry::where('lucky_draw_id', $drawId)
            ->with('customer')
            ->get()
            ->groupBy('customer_id')
            ->map(function ($entries) {
                $customer = $entries->first()->customer;
                $wonPrizes = $entries->where('is_winner', true)->pluck('prize_won')->filter()->toArray();

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'entries' => $entries->count(),
                    'won_prizes' => $wonPrizes,
                    'is_winner' => !empty($wonPrizes)
                ];
            })->values();
    }

    private function getAvailablePrizes($luckyDraw, $participants)
    {
        $wonPrizes = collect();
        foreach ($participants as $participant) {
            $wonPrizes = $wonPrizes->merge($participant['won_prizes']);
        }

        return collect($luckyDraw->prizes)->map(function ($prize) use ($wonPrizes) {
            return [
                'type' => $prize['type'],
                'name' => $prize['name'],
                'is_available' => !$wonPrizes->contains($prize['type'])
            ];
        });
    }

    // Additional method to get active draw
    public function getActiveDraw()
    {
        try {
            $activeDraw = LuckyDraw::active()->withCount(['entries', 'winners'])->first();

            return response()->json([
                'success' => true,
                'data' => $activeDraw
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get active draw.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Method to update lucky draw
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'status' => 'required|in:active,inactive',
                'max_entries_per_customer' => 'required|integer|min:1|max:10',
                'draw_date' => 'nullable|date|after:start_date',
            ]);

            $prizes = json_decode($request->prizes, true);
            if (empty($prizes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one prize is required.',
                    'errors' => ['prizes' => ['At least one prize is required.']]
                ], 422);
            }

            DB::beginTransaction();

            $luckyDraw = LuckyDraw::findOrFail($id);

            // If setting as active, deactivate all others
            if ($validated['status'] === 'active') {
                LuckyDraw::where('status', 'active')->where('id', '!=', $id)->update(['status' => 'inactive']);
            }

            $luckyDraw->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => $validated['status'],
                'max_entries_per_customer' => $validated['max_entries_per_customer'],
                'draw_date' => $validated['draw_date'],
                'prizes' => $prizes
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lucky draw updated successfully!',
                'data' => $luckyDraw
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update lucky draw. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
