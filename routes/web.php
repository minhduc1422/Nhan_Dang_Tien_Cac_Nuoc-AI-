<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ChatApi;

Route::get('/', function () {
    return view('auth.main');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('user.profile');
    Route::post('/profile/update-name', [AuthController::class, 'updateName'])->name('user.profile.update-name');
    Route::post('/profile/update-password', [AuthController::class, 'updatePassword'])->name('user.profile.update-password');
    Route::post('/profile/update-avatar', [AuthController::class, 'updateAvatar'])->name('user.profile.update-avatar');
    Route::get('/deposit', [AuthController::class, 'showDeposit'])->name('deposit');
    Route::post('/deposit', [AuthController::class, 'requestDeposit']);
    Route::post('/deposit-confirmation', [AuthController::class, 'depositConfirmation'])->name('deposit.confirmation');
    Route::get('/admin', [AuthController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AuthController::class, 'manageUsers'])->name('admin.users');
    Route::put('/admin/users/{id}/role', [AuthController::class, 'updateUserRole'])->name('admin.users.updateRole');
    Route::delete('/admin/users/{id}', [AuthController::class, 'deleteUser'])->name('admin.users.delete');
    Route::get('/admin/deposits', [AuthController::class, 'manageDeposits'])->name('admin.deposits');
    Route::put('/admin/deposits/{id}', [AuthController::class, 'updateDeposit'])->name('admin.deposits.update');
    Route::get('/admin/stats', [AuthController::class, 'stats'])->name('admin.stats');
    Route::get('/admin/histori', [AuthController::class, 'histori'])->name('admin.histori');
    Route::get('/admin/deposit_plans', [AuthController::class, 'manageDepositPlans'])->name('admin.deposit_plans');
    Route::get('/admin/deposit_plans/create', [AuthController::class, 'createDepositPlan'])->name('admin.deposit_plans.create');
    Route::post('/admin/deposit_plans', [AuthController::class, 'storeDepositPlan'])->name('admin.deposit_plans.store');
    Route::get('/admin/deposit_plans/{id}/edit', [AuthController::class, 'editDepositPlan'])->name('admin.deposit_plans.edit');
    Route::put('/admin/deposit_plans/{id}', [AuthController::class, 'updateDepositPlan'])->name('admin.deposit_plans.update');
    Route::delete('/admin/deposit_plans/{id}', [AuthController::class, 'destroyDepositPlan'])->name('admin.deposit_plans.destroy');
    Route::get('/admin/chatbot-config', [AuthController::class, 'chatbotConfig'])->name('admin.chatbot_config');
    Route::put('/admin/chatbot-config', [AuthController::class, 'updateChatbotConfig'])->name('admin.chatbot_config.update');
    Route::get('/admin/chatbot-config/get-key', [AuthController::class, 'getApiKey'])->name('admin.chatbot_config.get_key');
    Route::get('/admin/change-model', [AuthController::class, 'showDetectionModelConfig'])->name('admin.change_model');
    Route::get('/api/current-detection-model', [AuthController::class, 'getCurrentDetectionModel'])->name('api.current_detection_model');
    Route::get('/admin/metadata-config', [AuthController::class, 'metadataConfig'])->name('admin.metadata_config');
    Route::put('/admin/metadata-config', [AuthController::class, 'updateMetadataConfig'])->name('admin.metadata_config.update');
    Route::get('/admin/apks', [AuthController::class, 'manageApks'])->name('admin.apks');
    Route::get('/admin/apks/create', [AuthController::class, 'createApk'])->name('admin.apks.create');
    Route::post('/admin/apks', [AuthController::class, 'storeApk'])->name('admin.apks.store');
    Route::get('/admin/apks/{id}/edit', [AuthController::class, 'editApk'])->name('admin.apks.edit');
    Route::put('/admin/apks/{id}', [AuthController::class, 'updateApk'])->name('admin.apks.update');
    Route::delete('/admin/apks/{id}', [AuthController::class, 'destroyApk'])->name('admin.apks.destroy');

});
Route::post('/chat', [AuthController::class, 'chat'])->name('chat');

Route::post('/detect-money', [AuthController::class, 'detectMoney']);