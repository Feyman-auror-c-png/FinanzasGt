<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CheckShoppingItemRequest;
use App\Http\Requests\Finance\StoreShoppingItemRequest;
use App\Http\Requests\Finance\StoreShoppingListRequest;
use App\Models\Expense;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoppingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lists = ShoppingList::where('user_id', $request->user()->id)
            ->withCount('items')
            ->latest()
            ->get();

        return response()->json($lists);
    }

    public function store(StoreShoppingListRequest $request): JsonResponse
    {
        $list = ShoppingList::create($request->validated() + ['user_id' => $request->user()->id]);

        return response()->json($list, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $list = ShoppingList::with('items')->findOrFail($id);
        abort_if($list->user_id !== $request->user()->id, 403);

        return response()->json($list);
    }

    public function storeItem(StoreShoppingItemRequest $request, int $id): JsonResponse
    {
        $list = ShoppingList::findOrFail($id);
        abort_if($list->user_id !== $request->user()->id, 403);

        $item = $list->items()->create($request->validated());

        return response()->json($item, 201);
    }

    public function checkItem(CheckShoppingItemRequest $request, int $id, int $iid): JsonResponse
    {
        $list = ShoppingList::findOrFail($id);
        abort_if($list->user_id !== $request->user()->id, 403);

        $item = ShoppingItem::where('shopping_list_id', $list->id)->findOrFail($iid);

        if (! $request->has('actual_price')) {
            $item->checked_at = $item->checked_at ? null : now();
        } elseif (! $item->checked_at) {
            $item->checked_at = now();
        }

        $item->actual_price = $request->has('actual_price') ? $request->validated('actual_price') : $item->actual_price;
        $item->save();

        return response()->json($item);
    }

    public function sendToExpenses(Request $request, int $id): JsonResponse
    {
        $list = ShoppingList::with(['items' => fn ($query) => $query->whereNotNull('checked_at')->whereNotNull('actual_price')])->findOrFail($id);
        abort_if($list->user_id !== $request->user()->id, 403);

        $total = (float) $list->items->sum(fn ($item) => (float) $item->actual_price * (int) $item->quantity);

        if ($total <= 0) {
            return response()->json(['message' => 'No checked items with actual prices found.'], 422);
        }

        $expense = DB::transaction(function () use ($request, $list, $total) {
            $note = $list->name.': '.$list->items->map(fn ($item) => $item->name.' x'.$item->quantity)->implode(', ');

            return Expense::create([
                'user_id' => $request->user()->id,
                'amount' => $total,
                'category' => 'food',
                'note' => $note,
                'date' => now()->format('Y-m-d'),
            ]);
        });

        return response()->json(['message' => 'Shopping list sent to expenses.', 'expense' => $expense]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $list = ShoppingList::findOrFail($id);
        abort_if($list->user_id !== $request->user()->id, 403);

        $list->delete();

        return response()->json(['message' => 'Shopping list deleted.']);
    }
}
