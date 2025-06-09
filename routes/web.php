<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProposalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Profile Routes
    Route::get('/profile/edit', [AuthController::class, 'showEditForm'])->name('profile.edit');
    Route::put('/profile', [AuthController::class, 'update'])->name('profile.update');

    // Proposal Routes
    Route::resource('proposals', ProposalController::class);
    
    // Invitation Routes (as part of Proposals)
    Route::get('/proposals/invitations', [ProposalController::class, 'invitations'])->name('proposals.invitations');
    Route::post('/proposals/invitations/{invitation}/process', [ProposalController::class, 'processInvitation'])->name('proposals.process-invitation');
});
