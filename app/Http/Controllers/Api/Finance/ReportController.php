<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use App\Models\SavingsContribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function monthly(Request $request): JsonResponse
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $userId = $request->user()->id;

        $incomeTotal = (float) Income::where('user_id', $userId)->whereYear('date', $year)->whereMonth('date', $month)->sum('amount');
        $expensesByCategory = Expense::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('category, SUM(amount) as actual')
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category,
                'budgeted' => 0,
                'actual' => round((float) $row->actual, 2),
                'difference' => round(0 - (float) $row->actual, 2),
            ]);

        $expenseTotal = $expensesByCategory->sum('actual');
        $savingsContributions = (float) SavingsContribution::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        return response()->json([
            'month' => $month,
            'year' => $year,
            'income_total' => round($incomeTotal, 2),
            'expenses_by_category' => $expensesByCategory,
            'expense_total' => round($expenseTotal, 2),
            'balance' => round($incomeTotal - $expenseTotal - $savingsContributions, 2),
            'savings_contributions' => round($savingsContributions, 2),
        ]);
    }
}
