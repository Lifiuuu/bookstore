<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/attendance/record', [\App\Http\Controllers\AttendanceController::class, 'record']);
Route::post('/student/assign-nfc', [\App\Http\Controllers\StudentController::class, 'assignNfc']);
