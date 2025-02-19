<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\DataController;

Route::get('/', function () {
    return view('data');
});

Route::get('/data', [DataController::class, 'index']);

Route::get('/fetch-data', function () {
    $email = "fauzidwiseptiawan123@gmail.com"; // Ganti dengan email yang sesuai
    $response = Http::get("https://bsby.siglab.co.id/api/test-programmer", [
        'email' => $email
    ]);
    
    return $response->json();
});
