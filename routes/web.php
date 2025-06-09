<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\InvitationController;
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
    Route::post('/proposals/{proposal}/request', [ProposalController::class, 'requestToJoin'])->name('proposals.request');
    Route::delete('/proposals/request/{invitation}', [ProposalController::class, 'withdrawRequest'])->name('proposals.withdraw-request');

    Route::post('/invitations/{invitation}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/reject', [InvitationController::class, 'reject'])->name('invitations.reject');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
