<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController_app;

Route::prefix('app')->group(function () {
    Route::post('/login', [AuthController_app::class, 'login'])->name('api.app.login');
    Route::post('/register', [AuthController_app::class, 'register'])->name('api.app.register');
    Route::post('/detect-money', [AuthController_app::class, 'detectMoney'])->name('api.app.detect-money');
    Route::post('/deposit-plans', [AuthController_app::class, 'getDepositPlans'])->name('api.app.deposit-plans');
    Route::post('/deposit-confirmation', [AuthController_app::class, 'depositConfirmation'])->name('api.app.deposit-confirmation');
    Route::post('/profile', [AuthController_app::class, 'profile'])->name('api.app.profile');
    Route::post('/update-name', [AuthController_app::class, 'updateName'])->name('api.app.update-name');
    Route::post('/update-password', [AuthController_app::class, 'updatePassword'])->name('api.app.update-password');
    Route::post('/update-avatar', [AuthController_app::class, 'updateAvatar'])->name('api.app.update-avatar');
    Route::post('/delete-account', [AuthController_app::class, 'deleteAccount'])->name('api.app.delete-account');
    Route::post('/logout', [AuthController_app::class, 'logout'])->name('api.app.logout');
});

// Tuyến khác
Route::get('/current-detection-model', [App\Http\Controllers\AuthController::class, 'getCurrentDetectionModel'])->name('api.current_detection_model');