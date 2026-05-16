<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $totalIncome = (float) Income::where('user_id', $userId)->currentMonth()->sum('amount');
        $totalExpenses = (float) Expense::where('user_id', $userId)->currentMonth()->sum('amount');
        $monthlySavings = (float) SavingsContribution::where('user_id', $userId)->whereYear('date', now()->year)->whereMonth('date', now()->month)->sum('amount');
        $totalSaved = (float) SavingsGoal::where('user_id', $userId)->sum('saved_amount');
        $targetTotal = (float) SavingsGoal::where('user_id', $userId)->sum('target_amount');
        $monthsLeftAfterThisMonth = 12 - (int) now()->month;
        $yearEndProjection = $totalSaved + ($monthlySavings * $monthsLeftAfterThisMonth);

        $expensesByCategory = Expense::where('user_id', $userId)
            ->currentMonth()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $monthlyChartData = collect(range(5, 0))->map(function (int $offset) use ($userId) {
            $date = Carbon::now()->subMonths($offset);

            return [
                'label' => $date->format('M Y'),
                'income' => (float) Income::where('user_id', $userId)->whereYear('date', $date->year)->whereMonth('date', $date->month)->sum('amount'),
                'expenses' => (float) Expense::where('user_id', $userId)->whereYear('date', $date->year)->whereMonth('date', $date->month)->sum('amount'),
                'savings' => (float) SavingsContribution::where('user_id', $userId)->whereYear('date', $date->year)->whereMonth('date', $date->month)->sum('amount'),
            ];
        });

        return response()->json([
            'total_income' => round($totalIncome, 2),
            'total_expenses' => round($totalExpenses, 2),
            'monthly_savings' => round($monthlySavings, 2),
            'balance' => round($totalIncome - $totalExpenses - $monthlySavings, 2),
            'savings_goals_summary' => [
                'total_saved' => round($totalSaved, 2),
                'target_total' => round($targetTotal, 2),
                'progress' => $targetTotal > 0 ? round(($totalSaved / $targetTotal) * 100, 2) : 0,
                'monthly_savings' => round($monthlySavings, 2),
                'year_end_projection' => round($yearEndProjection, 2),
                'current_savings_month' => now()->translatedFormat('F Y'),
            ],
            'expenses_by_category' => $expensesByCategory,
            'monthly_chart_data' => $monthlyChartData,
        ]);
    }
}
