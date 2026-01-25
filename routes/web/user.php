<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::middleware(['end.impersonation', 'auth:web'])->group(function () {

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::controller(UserController::class)->group(function () {
        Route::get('/profile', 'profile')->name('users.profile');
        Route::post('users/delete', 'destroy')->name('users.delete');
        Route::post('/profile/update/info', 'update')->name('users.update.info');
        Route::post('/profile/update/password', 'update')->name('users.update.password');
    });

    Route::controller(ContactController::class)->group(function () {
        Route::post('/contact/destroy', 'destroy')->name('contact.delete');
        Route::post('/contact/create', 'create')->name('contact.create');
        Route::post('/contact/update', 'update')->name('contact.update');
    });

    Route::controller(EventController::class)->group(function () {
        Route::get('/events', 'index')->name('events.index');
        Route::get('/events/show/{id}', 'show')->name('events.show');
        Route::post('/events/inscription/{id}', 'inscription')->name('events.inscription');
    });

    Route::get('/participants', [ParticipantController::class, 'index'])
        ->name('participants.index');

    // Impersonation
    Route::get('/impersonate/{character}', [ImpersonationController::class, 'start'])
        ->name('impersonate.start');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])
        ->name('impersonate.stop');

    Route::controller(ParticipantController::class)->group(function () {
        Route::put('/participants/{id}/updatePassword', 'updatePassword')->name('participants.updatePassword');
    });

    Route::controller(BillController::class)->group(function () {
        Route::get('/bills', 'index')->name('bills.index');
        Route::get('/bills/{billId}', 'show')->name('bills.show');
    });

});
