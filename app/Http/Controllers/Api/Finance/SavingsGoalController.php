<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\AddSavingsFundsRequest;
use App\Http\Requests\Finance\StoreSavingsGoalRequest;
use App\Models\SavingsGoal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingsGoalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $goals = SavingsGoal::where('user_id', $userId)
            ->with(['contributions' => fn ($query) => $query->latest('date')->limit(6)])
            ->withSum(['contributions as saved_this_month' => fn ($query) => $query->where('user_id', $userId)->whereYear('date', now()->year)->whereMonth('date', now()->month)], 'amount')
            ->withSum(['contributions as saved_this_year' => fn ($query) => $query->where('user_id', $userId)->whereYear('date', now()->year)], 'amount')
            ->latest()
            ->get()
            ->map(function (SavingsGoal $goal) {
                $savedThisMonth = (float) ($goal->saved_this_month ?? 0);
                $monthsLeftAfterThisMonth = 12 - (int) now()->month;

                $goal->year_end_projection = round((float) $goal->saved_amount + ($savedThisMonth * $monthsLeftAfterThisMonth), 2);
                $goal->current_savings_month = now()->translatedFormat('F Y');

                return $goal;
            });

        return response()->json($goals);
    }

    public function store(StoreSavingsGoalRequest $request): JsonResponse
    {
        $goal = SavingsGoal::create($request->validated() + ['user_id' => $request->user()->id]);

        return response()->json($goal, 201);
    }

    public function addFunds(AddSavingsFundsRequest $request, int $id): JsonResponse
    {
        $goal = SavingsGoal::findOrFail($id);
        abort_if($goal->user_id !== $request->user()->id, 403);

        $amount = (float) $request->validated('amount');
        $remaining = (float) $goal->target_amount - (float) $goal->saved_amount;

        if ($remaining <= 0) {
            return response()->json(['message' => 'This savings goal is already completed.'], 422);
        }

        $appliedAmount = min($amount, $remaining);

        DB::transaction(function () use ($goal, $request, $appliedAmount) {
            $goal->increment('saved_amount', $appliedAmount);

            $goal->contributions()->create([
                'user_id' => $request->user()->id,
                'amount' => $appliedAmount,
                'date' => now()->format('Y-m-d'),
            ]);
        });

        $goal->refresh();

        return response()->json($goal);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $goal = SavingsGoal::findOrFail($id);
        abort_if($goal->user_id !== $request->user()->id, 403);

        $goal->delete();

        return response()->json(['message' => 'Savings goal deleted.']);
    }
}
