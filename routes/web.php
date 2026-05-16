<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('finance.dashboard');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/', fn () => redirect()->route('finance.dashboard'))->name('home');
    Route::get('/dashboard', fn () => Inertia::render('Finance/Dashboard'))->name('dashboard');
    Route::get('/income', fn () => Inertia::render('Finance/Income'))->name('income');
    Route::get('/expenses', fn () => Inertia::render('Finance/Expenses'))->name('expenses');
    Route::get('/savings', fn () => Inertia::render('Finance/SavingsGoals'))->name('savings');
    Route::get('/credit-cards', fn () => Inertia::render('Finance/CreditCards'))->name('credit-cards');
    Route::get('/shopping', fn () => Inertia::render('Finance/Shopping'))->name('shopping');
    Route::get('/reports', fn () => Inertia::render('Finance/Reports'))->name('reports');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
