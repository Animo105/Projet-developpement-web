<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\TreeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.any:web,character')->group(function () {

    Route::post('/cards/filters', [CardController::class, 'index'])->name('cards.filters');

    Route::post('/participants', [ParticipantController::class, 'store'])->name('participants.store');
    Route::get('/participants/{id}', [ParticipantController::class, 'show'])->name('participants.show');
    Route::put('/participants/{id}', [ParticipantController::class, 'update'])->name('participants.update');
    Route::delete('/participants/{id}', [ParticipantController::class, 'destroy'])->name('participants.destroy');

    Route::get('/skilltree/{classId}/{manaId}', [TreeController::class, 'show'])
        ->name('skilltree.show');
});
