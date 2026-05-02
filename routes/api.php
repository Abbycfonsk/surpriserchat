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
use App\Http\Controllers\UserController;
use App\Http\Controllers\OfferController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\GeniusController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SurpriseFileController;
use App\Http\Controllers\UserSkillController;

// Ruta de prueba
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});


//-------------------------------------------------
// SOLO PARA VERIFICAR EMAIL SOLO EN DESARROLLO
//-------------------------------------------------


Route::post('/dev/verify-email', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
    ]);

    $user = User::where('email', $request->email)->firstOrFail();

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'El email ya estaba verificado']);
    }

    $user->markEmailAsVerified();

    return response()->json(['message' => 'Email verificado correctamente']);
});



Route::post('/dev/get-reset-token', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $record = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();

    if (!$record) {
        return response()->json(['error' => 'No existe token para este email'], 404);
    }

    // ⚠️ IMPORTANTE:
    // Laravel genera el token real ANTES de hashearlo.
    // Como no lo tenemos, generamos uno nuevo y lo guardamos correctamente.

    $token = Str::random(60);

    DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->update([
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

    return response()->json([
        'token_real' => $token,
        'token_hash_guardado' => $record->token
    ]);
});

//-----------------------------------------------------------
//SOLO DESARROLLLO END
//-----------------------------------------------------------


/* ============================================================
 *  PUBLIC ROUTES (sin autenticación)
 * ============================================================ */

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::post('/forgot-password', function (Request $request) {
    // ...
});

Route::post('/reset-password', function (Request $request) {
    // ...
});
Route::post('/landing/subscribe', function (Illuminate\Http\Request $request) {
    try {
        $request->validate([
            'email' => 'required|email|unique:landing_subscribers,email'
        ]);

        \DB::table('landing_subscribers')->insert([
            'email' => $request->email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Si el email ya existe, no pasa nada
    }

    return redirect('https://surpriser-landing.netlify.app/gracias.html');
});

/* ============================================================
 *  AUTHENTICATED ROUTES (auth:sanctum)
 * ============================================================ */

Route::middleware('auth:sanctum')->group(function () {

    /* -------------------------
     *  USER
     * ------------------------- */
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', fn(Request $request) => response()->json($request->user()));
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::get('/users/{id}/dashboard', [UserController::class, 'dashboard']);

    /* -------------------------
     *  SURPRISES
     * ------------------------- */
    Route::post('/surprises', [SurpriseController::class, 'store']);
    Route::put('/surprises/{id}', [SurpriseController::class, 'update']);
    Route::get('/surprises', [SurpriseController::class, 'index']);
    Route::get('/surprises/{id}', [SurpriseController::class, 'show']);
    Route::delete('/surprises/{id}', [SurpriseController::class, 'destroy']);
    Route::post('/surprises/{id}/start', [SurpriseController::class, 'start']);
    Route::post('/surprises/{id}/deliver', [SurpriseController::class, 'deliver']);
    Route::post('/surprises/{id}/complete', [SurpriseController::class, 'complete']);
    Route::post('/surprises/{id}/cancel', [SurpriseController::class, 'cancel']);

    Route::get('/users/{id}/surprises-created', [SurpriseController::class, 'byCreator']);
    Route::get('/users/{id}/surprises-genius', [SurpriseController::class, 'byGenius']);

    /* -------------------------
     *  DISPUTES
     * ------------------------- */
    Route::post('/surprises/{id}/dispute', [DisputeController::class, 'openDispute']);
    Route::get('/disputes/{id}', [DisputeController::class, 'show']);
    Route::get('/my/disputes', [DisputeController::class, 'myDisputes']);
    Route::get('/users/{id}/disputes/creator', [DisputeController::class, 'disputesAsCreator']);
    Route::get('/users/{id}/disputes/genius', [DisputeController::class, 'disputesAsGenius']);

    /* -------------------------
     *  CONVERSATIONS & MESSAGES
     * ------------------------- */
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy']);

    Route::get('/conversations/{conversationId}/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::post('/surprises/{surpriseId}/messages', [MessageController::class, 'storeBySurprise']);
    Route::get('/messages/file/{id}', [MessageController::class, 'download']);

    /* -------------------------
     *  OFFERS
     * ------------------------- */
    Route::post('/surprises/{surpriseId}/offers', [OfferController::class, 'store']);
    Route::get('/surprises/{surpriseId}/offers', [OfferController::class, 'listBySurprise']);
    Route::post('/offers/{offerId}/counter', [OfferController::class, 'counterOffer']);
    Route::post('/offers/{offerId}/accept', [OfferController::class, 'accept']);

    /* -------------------------
     *  FILES
     * ------------------------- */
    Route::post('/surprises/{id}/files', [SurpriseFileController::class, 'store']);
    Route::get('/surprises/{id}/files', [SurpriseFileController::class, 'index']);
    Route::get('/files/{id}/download', [SurpriseFileController::class, 'download']);

    /* -------------------------
     *  NOTIFICATIONS
     * ------------------------- */
    Route::get('/users/{id}/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/users/{id}/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    /* -------------------------
     *  REVIEWS
     * ------------------------- */
    Route::post('/surprises/{id}/review', [ReviewController::class, 'store']);
    Route::get('/users/{id}/reviews', [ReviewController::class, 'byUser']);
    Route::get('/users/{id}/rating', [ReviewController::class, 'rating']);

    /* -------------------------
     *  SKILLS
     * ------------------------- */
    Route::get('/skills', [SkillController::class, 'index']);
    Route::get('/users/{id}/skills', [SkillController::class, 'userSkills']);
    Route::post('/users/{id}/skills', [SkillController::class, 'assignSkill']);
    Route::delete('/users/{id}/skills/{skillId}', [SkillController::class, 'removeSkill']);

    Route::post('/users/{id}/proposed-skills', [SkillController::class, 'updateProposedSkills']);

    Route::get('/skills/{skillId}/top', [TopSkillController::class, 'topBySkill']);
    Route::get('/skills/{skill}/ranking', [UserSkillController::class, 'ranking']);
    Route::get('/users/{user}/skills/{skill}/progress', [UserSkillController::class, 'progress']);
    Route::get('/users/{user}/skills/progress', [UserSkillController::class, 'allProgress']);
    Route::get('/users/{user}/skills/{skill}/history', [UserSkillController::class, 'history']);
    Route::get('/users/{user}/level', [UserSkillController::class, 'globalLevel']);
    Route::get('/users/{user}/skills/dashboard', [UserSkillController::class, 'dashboard']);

    /* -------------------------
     *  GENIUS SUGGESTIONS
     * ------------------------- */
    Route::get('/skills/{skillId}/genius-suggestions', [GeniusController::class, 'suggest']);
});


/* ============================================================
 *  GENIUS-ONLY ROUTES (auth + check.suspended)
 * ============================================================ */

Route::middleware(['auth:sanctum', 'check.suspended'])->group(function () {
    // Aquí van rutas EXCLUSIVAS del genio (si las tienes)
});


/* ============================================================
 *  ADMIN ROUTES (auth + admin)
 * ============================================================ */

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/disputes', [AdminController::class, 'listDisputes']);
    Route::post('/admin/disputes/{id}/resolve', [AdminController::class, 'resolveDispute']);
    Route::get('/admin/users', [AdminController::class, 'listUsers']);
    Route::post('/admin/users/{id}/ban', [AdminController::class, 'banUser']);
    Route::post('/admin/users/{id}/unban', [AdminController::class, 'unbanUser']);
    Route::get('/admin/surprises', [AdminController::class, 'listSurprises']);
    Route::post('/admin/surprises/{id}/force-cancel', [AdminController::class, 'forceCancel']);
});
