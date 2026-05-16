<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $query = Expense::query()
            ->where('user_id', $request->user()->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        $expenses = $query->latest('date')->get();

        return response()->json([
            'data' => $expenses,
            'total' => $expenses->sum('amount'),
            'by_category' => $expenses->groupBy('category')->map(fn ($items) => round($items->sum('amount'), 2))->values(),
            'breakdown' => $expenses->groupBy('category')->map(fn ($items, $category) => [
                'category' => $category,
                'total' => round($items->sum('amount'), 2),
            ])->values(),
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = Expense::create($request->validated() + ['user_id' => $request->user()->id]);

        return response()->json($expense, 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $expense = Expense::findOrFail($id);
        abort_if($expense->user_id !== $request->user()->id, 403);

        $expense->delete();

        return response()->json(['message' => 'Expense deleted.']);
    }
}
