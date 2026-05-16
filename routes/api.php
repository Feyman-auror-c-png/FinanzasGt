<?php

use App\Http\Controllers\Api\Finance\CreditCardController;
use App\Http\Controllers\Api\Finance\DashboardController;
use App\Http\Controllers\Api\Finance\ExpenseController;
use App\Http\Controllers\Api\Finance\IncomeController;
use App\Http\Controllers\Api\Finance\ReportController;
use App\Http\Controllers\Api\Finance\SavingsGoalController;
use App\Http\Controllers\Api\Finance\ShoppingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('finance')->group(function () {
    Route::get('income', [IncomeController::class, 'index']);
    Route::post('income', [IncomeController::class, 'store']);
    Route::delete('income/{id}', [IncomeController::class, 'destroy']);

    Route::get('expenses', [ExpenseController::class, 'index']);
    Route::post('expenses', [ExpenseController::class, 'store']);
    Route::delete('expenses/{id}', [ExpenseController::class, 'destroy']);

    Route::get('dashboard', DashboardController::class);

    Route::get('savings-goals', [SavingsGoalController::class, 'index']);
    Route::post('savings-goals', [SavingsGoalController::class, 'store']);
    Route::patch('savings-goals/{id}/add-funds', [SavingsGoalController::class, 'addFunds']);
    Route::delete('savings-goals/{id}', [SavingsGoalController::class, 'destroy']);

    Route::get('credit-cards', [CreditCardController::class, 'index']);
    Route::post('credit-cards', [CreditCardController::class, 'store']);
    Route::post('credit-cards/{id}/purchases', [CreditCardController::class, 'storePurchase']);
    Route::patch('credit-cards/{id}/pay', [CreditCardController::class, 'pay']);
    Route::delete('credit-cards/{id}/purchases/{pid}', [CreditCardController::class, 'destroyPurchase']);

    Route::get('shopping-lists', [ShoppingController::class, 'index']);
    Route::post('shopping-lists', [ShoppingController::class, 'store']);
    Route::get('shopping-lists/{id}', [ShoppingController::class, 'show']);
    Route::post('shopping-lists/{id}/items', [ShoppingController::class, 'storeItem']);
    Route::patch('shopping-lists/{id}/items/{iid}/check', [ShoppingController::class, 'checkItem']);
    Route::post('shopping-lists/{id}/send-to-expenses', [ShoppingController::class, 'sendToExpenses']);
    Route::delete('shopping-lists/{id}', [ShoppingController::class, 'destroy']);

    Route::get('reports/monthly', [ReportController::class, 'monthly']);
});
