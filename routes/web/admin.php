<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\SpendingController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['end.impersonation', 'auth:web', 'admin'])->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/accounts', 'index')->name('admin.accounts');
        Route::post('/admin/users/delete', 'destroy')->name('admin.users.delete');
    });

    Route::patch('/character/{id}/points', [CharacterController::class, 'updatePoints'])
        ->name('character.updatePoints');

    Route::put('/participants/{id}', [ParticipantController::class, 'update'])->name('participants.update');

    Route::controller(CardController::class)->group(function () {
        Route::get('/admin/cards', 'index')->name('cards.index');
        Route::get('/admin/cards/{id}', 'updateForm')->name('cards.updateForm');
        Route::put('/admin/cards/update', 'update')->name('cards.update');
        Route::post('/admin/cards/store', 'store')->name('cards.store');
        Route::put('/admin/cards/update/{id}', 'updateStock')->name('cards.update.stock');
        Route::delete('/admin/cards/destroy/{id}', 'destroy')->name('cards.destroy');
        Route::post('/admin/cards/give/{characterId}/{cardId}', [CardController::class, 'giveCard'])->name('cards.giveCard');
    });

    Route::prefix('admin/skilltree')->group(function () {
        Route::get('/{classId?}/{manaId?}', [TreeController::class, 'show'])
            ->name('admin.skilltree.show');

        Route::delete('/{classId}/{manaId}/{nodeId}', [TreeController::class, 'adminDeleteNode'])
            ->name('admin.skilltree.node.delete');

        Route::post('/{classId}/{manaId}/{nodeId}/unlock', [TreeController::class, 'adminUnlockNode'])
            ->name('admin.skilltree.node.unlock');

        Route::post('/{classId}/{manaId}/{nodeId}/lock', [TreeController::class, 'adminLockNode'])
            ->name('admin.skilltree.node.lock');

        Route::post('/{classId}/{manaId}/{nodeId}/children', [TreeController::class, 'adminAddNode'])
            ->name('admin.skilltree.node.children.create');

        Route::post('/attach/{cardId}/{nodeId}', [TreeController::class, 'adminAttachCard'])
            ->name('admin.skilltree.node.cards.attach');

        Route::delete('/{classId}/{manaId}/{nodeId}/cards/{cardId}', [TreeController::class, 'adminRemoveCard'])
            ->name('admin.skilltree.node.cards.remove');

        Route::get('/{classId}/{manaId}/available-cards', [TreeController::class, 'adminGetAvailableCards'])
            ->name('admin.skilltree.cards.available');

        Route::get('/{classId}/{manaId}/{nodeId}/lockable-nodes', [TreeController::class, 'adminGetLockableNodes'])
            ->name('admin.skilltree.nodes.lockable');
    });

    Route::get('/cards/attach/{nodeId}', [CardController::class, 'index'])->name('cards.attach');
    Route::get('/cards/give/{characterId}', [CardController::class, 'index'])->name('cards.give');

    Route::controller(EventController::class)->group(function () {
        Route::get('/events/create', 'create')->name('admin.events.create');
        Route::get('/events/edit/{id}', 'edit')->name('admin.events.edit');
        Route::post('/events/update/{id}', 'update')->name('admin.events.update');
        Route::post('/events/store', 'store')->name('admin.events.store');
        Route::post('events/destroy', 'destroy')->name('admin.events.destroy');
    });

    Route::controller(SpendingController::class)->group(function () {
        Route::post('/spending/store', 'store')->name('admin.spending.store');
        Route::post('/spending/update', 'update')->name('admin.spending.update');
        Route::post('/spending/destroy', 'destroy')->name('admin.spending.destroy');
    });


    Route::controller(GalleryController::class)->group(function () {
        Route::post('/gallery/store/{id}', 'addPhoto')->name('api.gallery.addPhoto');
        Route::delete('/gallery/destroy/picture/{id}', 'destroyPicture')->name('gallery.picture.destroy');
        Route::post('/gallery/newAlbum', 'store')->name('gallery.store');
        Route::delete('/gallery/destroy/{id}', 'destroy')->name('gallery.destroy');
    });

    Route::controller(BillController::class)->group(function () {
        Route::post('/bills/{bill}/payments', [BillController::class, 'addPayment'])->name('bills.payments.add');
        Route::delete('/bills/payments/{id}', 'deletePayment')->name('bills.payments.delete');
        Route::patch('/bills/{bill}/status', [BillController::class, 'updateStatus'])->name('bills.status.update');
    });

});
