<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ProposalController::class, 'index'])->name('dashboard');
    Route::get('/my-topics', [ProposalController::class, 'myTopics'])->name('my-topics');
    Route::get('/find-supervisor', [ProposalController::class, 'findSupervisor'])->name('find-supervisor');
    Route::get('/my-invitations', [InvitationController::class, 'myInvitations'])->name('my-invitations');

    Route::get('/proposals/{proposal}', [ProposalController::class, 'show'])->name('proposals.show');
    Route::post('/proposals', [ProposalController::class, 'store'])->name('proposals.store');
    Route::put('/proposals/{proposal}', [ProposalController::class, 'update'])->name('proposals.update');
    Route::delete('/proposals/{proposal}', [ProposalController::class, 'destroy'])->name('proposals.destroy');
    Route::post('/proposals/{proposal}/request', [ProposalController::class, 'requestToJoin'])->name('proposals.request');
    Route::delete('/proposals/request/{invitation}', [ProposalController::class, 'withdrawRequest'])->name('proposals.withdraw-request');

    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::post('/invitations/{invitation}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/reject', [InvitationController::class, 'reject'])->name('invitations.reject');
    Route::delete('/invitations/{invitation}/withdraw', [ProposalController::class, 'withdrawInvitation'])->name('invitations.withdraw');
    Route::delete('/invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');

    Route::get('/profile', [UserController::class, 'viewProfile'])->name('profile.view');
    Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');
    Route::patch('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile', [UserController::class, 'destroyProfile'])->name('profile.destroy');

    // (moved to admin prefix group below)
});

// Admin prefixed routes with name alias for backward compatibility
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', AdminUserController::class);
});

require __DIR__.'/auth.php';
