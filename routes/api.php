<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurpriseController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\NotificationController;
use App\Models\Surprise;
use App\Models\User;
use App\Http\Controllers\TopSkillController;

// Ruta de prueba
/*Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});*/







Route::post('/register', [AuthController::class, 'register']); //PARA REGISTRARTE
Route::post('/login', [AuthController::class, 'login']); //PARA LOGUEARTE







Route::get('/surprises/by-genius/{id}', function ($id) {
    return Surprise::where('genius_id', $id)->get();
})->middleware('auth:sanctum'); // ruta rara, nose si sirve o no aun


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']); //DESLOGUEARTE
    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    }); //INFO DEL USUARIO LOGUEADO
    Route::post('/surprises', [SurpriseController::class, 'store']); //CREAR UNA SORPRESA
    Route::put('/surprises/{id}', [SurpriseController::class, 'update']); //MODIFICAR UNA SORPRESA
    Route::get('/conversations', [ConversationController::class, 'index']); //LISTAR TUS CONVERSACIONES
    Route::post('/conversations', [ConversationController::class, 'store']); //CREAR UNA CONVERSACIÓN
    Route::get('/messages/{conversation}', [MessageController::class, 'index']); //LISTAR MENSAJES DE UNA CONVERSACIÓN
    Route::post('/messages', [MessageController::class, 'store']); //ENVIAR MENSAJES
    Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy']); //ELIMINAR UNA CONVERSACIÓN/MENSAJES
    Route::get('/skills', [SkillController::class, 'index']); //LISTADO DE SKILLS
    Route::get('/users/{id}/rating', [\App\Http\Controllers\ReviewController::class, 'rating']); //rating del usuario logueado, falta probar si sale todo las valoraciones como creador y genio o solo genio
    Route::get('/genius/profile', function (Request $request) {
        $user = User::find($request->user()->id);

        return response()->json([
            'level' => $user->genius_level,
            'points' => $user->genius_points,
            'total_surprises' => $user->genius_total_surprises,
            'avg_rating' => $user->genius_avg_rating,
            'allowed_sizes' => $user->allowedSurpriseSizes(),
            'max_active' => $user->maxActiveSurprises(),
            'can_receive_initial' => $user->canReceiveInitialPayment(),
            'can_accept_urgent' => $user->canAcceptUrgent(),
            'can_accept_premium' => $user->canAcceptPremium(),
        ]);
    }); //PERFIL DEL GENIO
    Route::post('/surprises/{id}/review', [\App\Http\Controllers\ReviewController::class, 'store']); //CREAR UNA RESEÑA
    Route::get('/surprises/{id}/offers', [\App\Http\Controllers\OfferController::class, 'listBySurprise']); //LISTAR OFERTAS DE UNA SORPRESA
    Route::get('/users/{id}/notifications', [NotificationController::class, 'index']); //LISTAR LAS NOTIFICACIONES DE UN USUARIO
    Route::get('/users/{id}/reviews', [\App\Http\Controllers\ReviewController::class, 'byUser']); //LISTAR RESEÑAS DE UN USUARIO
    Route::get('/surprises', [SurpriseController::class, 'index']); // LISTADO DE TODAS LAS SORPRESAS EN TODOS LOS ESTADOS Y DE TODOS LOS USUARIOS
    Route::get('/surprises/{id}', [SurpriseController::class, 'show']); //BUSCA UNA SORPRESA POR SU ID
    Route::get('/users/{id}/surprises-created', [SurpriseController::class, 'byCreator']); //LISTAR LAS SORPRESAS CREADAS POR UN USUSARIO CONCRETO
    Route::get('/users/{id}/surprises-genius', [SurpriseController::class, 'byGenius']); //LISTA LAS SOPRESAS DONDE EL USUSARIO ACTUÓ COMO GENIO








    Route::post('/surprises/{id}/complete', [SurpriseController::class, 'complete'])
        ->middleware('auth:sanctum');


    Route::get('/skills/{skillId}/top', [TopSkillController::class, 'topBySkill']); //LISTA TOP10 DE USUARIO X SKILL

    // Ver skills (propuestas + activas)
    Route::get('/users/{id}/skills', [SkillController::class, 'userSkills']);

    // Actualizar skills propuestas (genio)
    Route::post('/users/{id}/proposed-skills', [SkillController::class, 'updateProposedSkills'])
        ->middleware('auth:sanctum');

    // Ofertas
    Route::post('/surprises/{id}/offers', [\App\Http\Controllers\OfferController::class, 'store']);
    Route::post('/offers/{id}/accept', [\App\Http\Controllers\OfferController::class, 'accept']);
    // Notificaciones
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/users/{id}/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    Route::delete('/surprises/{id}', [SurpriseController::class, 'destroy']);
    Route::post('/surprises/{id}/files', [SurpriseController::class, 'addFile']);
    Route::post('/surprises/{id}/start', [SurpriseController::class, 'start']);
    Route::post('/surprises/{id}/deliver', [SurpriseController::class, 'deliver']);
    Route::post('/surprises/{id}/complete', [SurpriseController::class, 'complete']);
    Route::post('/surprises/{id}/cancel', [SurpriseController::class, 'cancel']);

    Route::post('/users/{id}/skills', [SkillController::class, 'assignSkill']);
    Route::delete('/users/{id}/skills/{skillId}', [SkillController::class, 'removeSkill']);
    Route::get('/users/{id}/skills', [SkillController::class, 'userSkills']);

    Route::get('/users/{user}/skills/{skill}/progress', [\App\Http\Controllers\UserSkillController::class, 'progress']); /*solo se usa Use arriba si el nombre del controlador no lleva ruta, sino asi te evitas poner un Use */
    Route::get('/users/{user}/skills/progress', [\App\Http\Controllers\UserSkillController::class, 'allProgress']);
    Route::get('/skills/{skill}/ranking', [\App\Http\Controllers\UserSkillController::class, 'ranking']);
    Route::get('/users/{user}/skills/{skill}/history', [\App\Http\Controllers\UserSkillController::class, 'history']);
    Route::get('/users/{user}/level', [\App\Http\Controllers\UserSkillController::class, 'globalLevel']);
    Route::get('/users/{user}/skills/dashboard', [\App\Http\Controllers\UserSkillController::class, 'dashboard']);

    Route::get('/skills/{skillId}/genius-suggestions', [\App\Http\Controllers\GeniusController::class, 'suggest']);

    Route::post('/surprises/{id}/files', [\App\Http\Controllers\SurpriseFileController::class, 'store']);
    Route::get('/surprises/{id}/files', [\App\Http\Controllers\SurpriseFileController::class, 'index']);
    Route::get('/files/{id}/download', [\App\Http\Controllers\SurpriseFileController::class, 'download']);

    Route::get('/users/{id}/dashboard', [\App\Http\Controllers\UserController::class, 'dashboard']);
});
