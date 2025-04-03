<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('auth.main');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route mới được thêm
Route::middleware('auth')->group(function () {
    Route::get('/deposit', [AuthController::class, 'showDeposit'])->name('deposit');
    Route::post('/deposit', [AuthController::class, 'requestDeposit']);
    Route::post('/deposit-confirmation', [AuthController::class, 'depositConfirmation'])->name('deposit.confirmation'); // Route mới
    Route::get('/admin', [AuthController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/users', [AuthController::class, 'manageUsers'])->name('admin.users');
    Route::get('/admin/deposits', [AuthController::class, 'manageDeposits'])->name('admin.deposits');
    Route::get('/admin/stats', [AuthController::class, 'depositStats'])->name('admin.stats');
    Route::put('/admin/deposits/{id}', [AuthController::class, 'updateDeposit'])->name('admin.deposits.update');
});

Route::post('/detect-money', [AuthController::class, 'detectMoney']);

Route::post('/chat', function (Request $request) {
    $question = $request->input('question');
    if (!$question) {
        return response()->json(['response' => 'Vui lòng cung cấp câu hỏi!'], 400);
    }

    try {
        $response = Http::post('http://localhost:60074/chat', [
            'question' => $question
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json(['response' => $data['response']]);
        } else {
            return response()->json(['response' => 'Lỗi từ chatbot FastAPI: ' . $response->body()], 500);
        }
    } catch (\Exception $e) {
        return response()->json(['response' => 'Lỗi khi kết nối với chatbot: ' . $e->getMessage()], 500);
    }
});