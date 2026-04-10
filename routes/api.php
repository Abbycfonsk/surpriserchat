<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurpriseController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\NotificationController;

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

Route::get('/users/{user}/skills/{skill}/progress', [\App\Http\Controllers\UserSkillController::class, 'progress']); /*solo se usa Use arriba si el nombre del controlador no lleva ruta, sino asi te evitas poner un Use */
Route::get('/users/{user}/skills/progress', [\App\Http\Controllers\UserSkillController::class, 'allProgress']);
Route::get('/skills/{skill}/ranking', [\App\Http\Controllers\UserSkillController::class, 'ranking']);
Route::get('/users/{user}/skills/{skill}/history', [\App\Http\Controllers\UserSkillController::class, 'history']);
Route::get('/users/{user}/level', [\App\Http\Controllers\UserSkillController::class, 'globalLevel']);
Route::get('/users/{user}/skills/dashboard', [\App\Http\Controllers\UserSkillController::class, 'dashboard']);

Route::post('/surprises/{id}/accept', [SurpriseController::class, 'accept']);
Route::post('/surprises/{id}/start', [SurpriseController::class, 'start']);
Route::post('/surprises/{id}/deliver', [SurpriseController::class, 'deliver']);
Route::post('/surprises/{id}/complete', [SurpriseController::class, 'complete']);
Route::post('/surprises/{id}/cancel', [SurpriseController::class, 'cancel']);

Route::post('/surprises/{id}/review', [\App\Http\Controllers\ReviewController::class, 'store']);
Route::get('/users/{id}/reviews', [\App\Http\Controllers\ReviewController::class, 'byUser']);
Route::get('/users/{id}/rating', [\App\Http\Controllers\ReviewController::class, 'rating']);

Route::get('/users/{id}/dashboard', [\App\Http\Controllers\UserController::class, 'dashboard']);

Route::get('/skills/{skillId}/genius-suggestions', [\App\Http\Controllers\GeniusController::class, 'suggest']);

Route::post('/surprises/{id}/files', [\App\Http\Controllers\SurpriseFileController::class, 'store']);
Route::get('/surprises/{id}/files', [\App\Http\Controllers\SurpriseFileController::class, 'index']);
Route::get('/files/{id}/download', [\App\Http\Controllers\SurpriseFileController::class, 'download']);

// Ofertas
Route::post('/surprises/{id}/offers', [\App\Http\Controllers\OfferController::class, 'store']);
Route::get('/surprises/{id}/offers', [\App\Http\Controllers\OfferController::class, 'listBySurprise']);
Route::post('/offers/{id}/accept', [\App\Http\Controllers\OfferController::class, 'accept']);



// Notificaciones
Route::get('/users/{id}/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::post('/users/{id}/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

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
