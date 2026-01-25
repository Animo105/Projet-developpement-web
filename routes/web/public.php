<?php

use App\Http\Controllers\GalleryController;
use Illuminate\Support\Facades\Route;

Route::middleware('end.impersonation')->group(function () {

    // La route /login est celle vers laquelle on est redirigé quand on tente d’accéder à une page non autorisée.
    Route::get('/login', fn() => redirect('/'))->name('login');// On redirige vers home car on pas /login (solution duck-tape)

    Route::inertia('/', 'Home')->name('home');
});

Route::controller(GalleryController::class)->group(function () {
    Route::get('/gallery', 'index')->name('gallery.index');
});
