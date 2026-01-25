<?php

use App\Http\Controllers\Auth\CharacterLoginController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\TreeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:character')->group(function () {
    Route::post('/character/login', [CharacterLoginController::class, 'login'])
        ->name('character.login');
});

Route::middleware('auth:character')->group(function () {


    Route::post('/character/logout', [CharacterLoginController::class, 'logout'])
        ->name('character.logout');

    Route::controller(CharacterController::class)->group(function () {
        Route::put('/character/{characterId}', 'update')->name('character.update');
        Route::get('/character/profile', 'showProfile')->name('character.profile');
        Route::delete('/character/{participantId}', 'destroy')->name('character.destroy');


    });

    Route::post('/skilltree/{classId}/{manaId}/{nodeId}/unlock', [TreeController::class, 'nodeUnlock'])
        ->name('skilltree.node.unlock');

    Route::delete('/character/{participantId}', [CharacterController::class, 'destroy'])
        ->name('character.destroy');

    Route::get('/cards', [CardController::class, 'index'])->name('cards.index');
    Route::get('/skilltree/{classId}/{manaId}', [TreeController::class, 'show'])
        ->name('skilltree.show');
});
