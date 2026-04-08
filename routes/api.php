<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurpriseController;
use App\Http\Controllers\SkillController;

// Ruta de prueba
/*Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});*/




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::get('/surprises', [SurpriseController::class, 'index']);
Route::get('/surprises/{id}', [SurpriseController::class, 'show']);
Route::post('/surprises', [SurpriseController::class, 'store']);
Route::put('/surprises/{id}', [SurpriseController::class, 'update']);
Route::delete('/surprises/{id}', [SurpriseController::class, 'destroy']);
Route::post('/surprises/{id}/files', [SurpriseController::class, 'addFile']);
Route::get('/users/{id}/surprises-created', [SurpriseController::class, 'byCreator']);
Route::get('/users/{id}/surprises-genius', [SurpriseController::class, 'byGenius']);


Route::get('/skills', [SkillController::class, 'index']);
Route::post('/users/{id}/skills', [SkillController::class, 'assignSkill']);
Route::delete('/users/{id}/skills/{skillId}', [SkillController::class, 'removeSkill']);
Route::get('/users/{id}/skills', [SkillController::class, 'userSkills']);



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/messages/{conversation}', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy']);
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });
});
