<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::post('/chat', function (Request $request) {
    $question = $request->input('question');

    if (!$question) {
        return response()->json(['error' => 'Vui lòng nhập câu hỏi!'], 400);
    }

    // Lấy URL FastAPI từ .env
    $fastapi_url = env('FASTAPI_URL', 'http://127.0.0.1:8000');

    // Gửi câu hỏi đến FastAPI
    $response = Http::post("$fastapi_url/chat", [
        'question' => $question
    ]);

    return response()->json($response->json());
});
