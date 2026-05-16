<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreIncomeRequest;
use App\Models\Income;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Income::query()
            ->where('user_id', $request->user()->id)
            ->currentMonth()
            ->latest('date')
            ->get()
            ->groupBy('type');

        return response()->json([
            'salary' => $items->get('salary', collect())->values(),
            'extra' => $items->get('extra', collect())->values(),
            'total' => $items->flatten(1)->sum('amount'),
        ]);
    }

    public function store(StoreIncomeRequest $request): JsonResponse
    {
        $income = Income::create($request->validated() + ['user_id' => $request->user()->id]);

        return response()->json($income, 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $income = Income::findOrFail($id);
        abort_if($income->user_id !== $request->user()->id, 403);

        $income->delete();

        return response()->json(['message' => 'Income deleted.']);
    }
}
