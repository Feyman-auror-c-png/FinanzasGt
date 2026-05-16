<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCreditCardRequest;
use App\Http\Requests\Finance\StoreCreditPurchaseRequest;
use App\Models\CreditCard;
use App\Models\CreditPurchase;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditCardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cards = CreditCard::where('user_id', $request->user()->id)
            ->with(['purchases' => fn ($query) => $query->where('user_id', $request->user()->id)->latest('date')])
            ->withSum(['purchases as unpaid_total' => fn ($query) => $query->where('user_id', $request->user()->id)->whereNull('paid_at')], 'amount')
            ->latest()
            ->get();

        return response()->json($cards);
    }

    public function store(StoreCreditCardRequest $request): JsonResponse
    {
        $card = CreditCard::create($request->validated() + ['user_id' => $request->user()->id]);

        return response()->json($card, 201);
    }

    public function storePurchase(StoreCreditPurchaseRequest $request, int $id): JsonResponse
    {
        $card = CreditCard::findOrFail($id);
        abort_if($card->user_id !== $request->user()->id, 403);

        $purchase = $card->purchases()->create($request->validated() + ['user_id' => $request->user()->id]);

        return response()->json($purchase, 201);
    }

    public function pay(Request $request, int $id): JsonResponse
    {
        $card = CreditCard::with(['purchases' => fn ($query) => $query->where('user_id', $request->user()->id)->whereNull('paid_at')])->findOrFail($id);
        abort_if($card->user_id !== $request->user()->id, 403);

        $total = (float) $card->purchases->sum('amount');

        if ($total <= 0) {
            return response()->json(['message' => 'No unpaid purchases found.', 'total' => 0]);
        }

        $expense = DB::transaction(function () use ($card, $request, $total) {
            CreditPurchase::where('credit_card_id', $card->id)->where('user_id', $request->user()->id)->whereNull('paid_at')->update(['paid_at' => now()]);

            return Expense::create([
                'user_id' => $request->user()->id,
                'amount' => $total,
                'category' => 'services',
                'note' => 'Credit card payment: '.$card->name.' - '.$card->bank,
                'date' => now()->format('Y-m-d'),
            ]);
        });

        return response()->json(['message' => 'Credit card paid.', 'total' => round($total, 2), 'expense' => $expense]);
    }

    public function destroyPurchase(Request $request, int $id, int $pid): JsonResponse
    {
        $card = CreditCard::findOrFail($id);
        abort_if($card->user_id !== $request->user()->id, 403);

        $purchase = CreditPurchase::where('credit_card_id', $card->id)->where('user_id', $request->user()->id)->findOrFail($pid);
        $purchase->delete();

        return response()->json(['message' => 'Purchase deleted.']);
    }
}
