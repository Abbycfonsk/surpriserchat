<?php

namespace App\Http\Controllers;

use App\Models\Surprise;
use App\Models\SurpriseFile;
use Illuminate\Http\Request;
use App\Helpers\Notify;
use App\Services\GeniusLevelService;
use App\Services\NotificationEvents;
use App\Services\AuditService;
use App\Services\SanitizerService;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;




class SurpriseController extends Controller
{
    protected $geniusLevelService;

    public function __construct(GeniusLevelService $geniusLevelService)
    {
        $this->geniusLevelService = $geniusLevelService;
    }

    // Listar todas las sorpresas
public function index()
{
    $surprises = Surprise::with([
        'creator',
        'genius',
        'files',
        'review',
        'offers'
    ])
    ->withCount('ads')
    ->get();

    return response()->json([
        'success' => true,
        'data' => $surprises
    ]);
}
public function feed(Request $request)
{
    $query = Surprise::query()
        ->where('status', 'open')
        ->select([
            'id',
            'title',
            'status',
            'deadline',
            'size',
            'skill_id',
            'is_urgent',
            'header_image',
            'creator_id',

            // ubicación
            'target_city',
            'target_country',
            'target_province',
        ])
        ->withCount('ads')
        ->with([
            'skill:id,name',
            'creator:id,avatar'
        ]);

    if ($request->filled('skill_id')) {
        $query->where('skill_id', $request->skill_id);
    }

    if ($request->filled('is_urgent')) {
        $query->where('is_urgent', 1);
    }

    if ($request->filled('featured') && $request->featured == 1) {
        $query->whereHas('ads');
    }

    if ($request->filled('size')) {
        $query->where('size', $request->size);
    }

    if ($request->filled('province')) {
        $query->where('target_province', 'LIKE', '%' . $request->province . '%');
    }

    // orden
    $query->orderByDesc('ads_count');
    $query->orderByDesc('is_urgent');

    if ($request->filled('order_deadline')) {
        $query->orderBy('deadline', $request->order_deadline);
    } else {
        $query->orderBy('deadline', 'asc');
    }

    if ($request->filled('order_size')) {
        $query->orderByRaw("
            CASE size
                WHEN 'SMALL' THEN 1
                WHEN 'MEDIUM' THEN 2
                WHEN 'LARGE' THEN 3
                WHEN 'PREMIUM' THEN 4
                ELSE 99
            END " . ($request->order_size === 'desc' ? 'DESC' : 'ASC')
        );
    }

    $surprises = $query->get()->map(function ($surprise) {

        $xp = 10; // base XP

        // tamaño
        if ($surprise->size === 'MEDIUM') $xp += 10;
        if ($surprise->size === 'LARGE') $xp += 20;
        if ($surprise->size === 'PREMIUM') $xp += 40;

        // urgencia
        if ($surprise->is_urgent) $xp += 15;

        // ads (valor añadido de marketplace)
        if ($surprise->ads_count > 0) $xp += 10;

        // opcional: bonus si está destacada
        if ($surprise->ads_count > 0) $xp += 5;

        $surprise->xp_value = $xp;

        return $surprise;
    });

    return response()->json([
        'success' => true,
        'data' => $surprises
    ]);
}
    // Ver una sorpresa por ID
   public function show($id)
{
    $surprise = Surprise::with([
        'creator',
        'skill:id,name',
        'files'
    ])
    ->withCount('ads')
    ->findOrFail($id);

    return response()->json([
        'success' => true,
        'data' => $surprise
    ]);
}

    // Crear una sorpresa
public function store(Request $request)
{
    // ============================
    // LOG DE DEPURACIÓN
    // ============================
    \Log::info('SURPRISE STORE REQUEST RECEIVED', [
        'raw_body' => $request->all(),
        'json' => json_decode($request->getContent(), true),
        'user' => $request->user()?->id,
        'headers' => $request->headers->all()
    ]);

    try {

    // ============================
// VALIDACIÓN
// ============================
$validated = $request->validate([
    'creator_id' => 'required|exists:users,id',
    'genius_id' => 'nullable|exists:users,id',
    'title' => 'required|string',
    'description' => 'nullable|string',
    'status' => 'sometimes|string|in:open,in_progress,delivered,completed,cancelled',
    'price' => 'nullable|numeric',
    'deadline' => 'nullable|date',
    'skill_id' => 'required|exists:skills,id',
    'size' => 'required|string|in:SMALL,MEDIUM,LARGE,PREMIUM',
    'is_urgent' => 'nullable|boolean',
    'highlight' => 'nullable|boolean',
    'header_image' => 'required'
]);


// ============================
// FIX: is_urgent no puede ser null en DB
// ============================
if (!isset($validated['is_urgent']) || $validated['is_urgent'] === null) {
    $validated['is_urgent'] = false;
}

        // ============================
        // SANITIZACIÓN
        // ============================
        $validated['title'] = SanitizerService::clean($validated['title']);
        $validated['description'] = SanitizerService::clean($validated['description']);

        // ============================
        // IMAGEN DE CABECERA
        // ============================
      if ($request->hasFile('header_image')) {
    $path = $request->file('header_image')->store('surprises/headers', 'public');
    $validated['header_image'] = $path;
} else {
    $validated['header_image'] = $request->input('header_image');
}

        // ============================
        // ESTADO INICIAL
        // ============================
        $validated['status'] = 'open';

        // ============================
        // CREATOR NO PUEDE SER GENIUS
        // ============================
        if (!empty($validated['genius_id']) && $validated['creator_id'] == $validated['genius_id']) {
            return response()->json([
                'error' => 'Creator and genius cannot be the same user'
            ], 400);
        }

        // ============================
        // CREAR SORPRESA
        // ============================
        $surprise = Surprise::create($validated);

        AuditService::log(
            'surprise_created',
            'Surprise',
            $surprise->id,
            null,
            $surprise->toArray()
        );

        NotificationEvents::surpriseCreated($surprise);

        // ============================
        // ¿QUIERE DESTACAR LA SORPRESA?
        // ============================
        if (isset($validated['highlight']) && $validated['highlight'] === true) {

            \Log::info('SURPRISE HIGHLIGHT REQUEST', [
                'surprise_id' => $surprise->id,
                'creator_id' => $validated['creator_id']
            ]);

            $adRequest = new Request([
                'surprise_id' => $surprise->id
            ]);

            $adResponse = app(\App\Http\Controllers\SurpriseAdController::class)->store($adRequest);

            if ($adResponse->getStatusCode() !== 200) {

                \Log::warning('HIGHLIGHT FAILED — DELETING SURPRISE', [
                    'surprise_id' => $surprise->id,
                    'ad_response' => $adResponse->getContent()
                ]);

                $surprise->delete();

                return response()->json([
                    'error' => 'You tried to highlight this surprise but you have no ads available'
                ], 403);
            }
        }

        // ============================
        // RESPUESTA OK
        // ============================
        return response()->json([
            'success' => true,
            'message' => 'Surprise created successfully',
            'data' => $surprise
        ]);

    } catch (\Throwable $e) {

        // ============================
        // LOG DE ERROR REAL
        // ============================
        \Log::error('SURPRISE STORE ERROR', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'error' => 'Internal server error',
            'details' => $e->getMessage()
        ], 500);
    }
}
public function update(Request $request, $id)
{
    \Log::info('ESTOY EN EL MÉTODO UPDATE');
    $surprise = Surprise::findOrFail($id);
    $user = $request->user();

    $isCreator = $user->id === $surprise->creator_id;
    $isGenius = $user->id === $surprise->genius_id;

    // El genio NO puede modificar
    if ($isGenius) {
        return response()->json(['error' => 'Genius cannot modify surprise details'], 403);
    }

    // No se puede modificar si está completada
    if ($surprise->status === 'completed') {
        return response()->json(['error' => 'Completed surprises cannot be modified'], 400);
    }

    // No se puede modificar si está entregada
    if ($surprise->status === 'delivered') {
        return response()->json(['error' => 'Delivered surprises cannot be modified'], 400);
    }

    // Solo el creador puede modificar
    if (!$isCreator) {
        return response()->json(['error' => 'Only the creator can modify the surprise'], 403);
    }

    // Validación según estado
    $rules = [];

    if ($surprise->status === 'open') {
        $rules = [
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:1',
            'deadline' => 'nullable|date',
            'size' => 'nullable|string|in:SMALL,MEDIUM,LARGE,PREMIUM',
            'is_urgent' => 'nullable|boolean',
            'target_name' => 'nullable|string|max:100',
            'target_city' => 'nullable|string|max:100',
            'target_country' => 'nullable|string|max:100',
            'target_lat' => 'nullable|numeric',
            'target_lng' => 'nullable|numeric',

            // Puede ser string (galería) o file (personalizada)
            'header_image' => 'nullable'
        ];
    }

    if ($surprise->status === 'in_progress') {
        $rules = [
            'description' => 'nullable|string',
            'deadline' => 'nullable|date|after_or_equal:today',
            'header_image' => 'nullable'
        ];
    }

    $validated = $request->validate($rules);

    // No permitir cambiar estado ni genius
    unset($validated['status'], $validated['genius_id']);

    // Sanitización
    foreach (['title', 'description', 'target_name', 'target_city', 'target_country'] as $field) {
        if (isset($validated[$field])) {
            $validated[$field] = SanitizerService::clean($validated[$field]);
        }
    }

    // Guardar valores previos
    $old = $surprise->getOriginal();

    // ============================
    // PROCESAR HEADER IMAGE
    // ============================

    \Log::info('FILES RECIBIDOS', $request->allFiles());

    // 1) Si envían archivo → subir, comprimir y convertir a webp
    if ($request->hasFile('header_image')) {

        \Log::info('ENTRANDO A PROCESAR HEADER IMAGE');

        $manager = new ImageManager(new Driver());

        $img = $manager->read($request->file('header_image')->getRealPath());

       $img = $img->scale(width: 1000);

        // Codificar a WebP
        $encoded = $img->encode(new WebpEncoder(quality: 80));

        // Convertir a binario REAL
        $binary = $encoded->toString();

        // Guardar
        $path = 'surprise_headers/' . uniqid() . '.webp';
        $ok = Storage::disk('public')->put($path, $binary);

        \Log::info('RESULTADO GUARDADO HEADER', [
            'ok' => $ok,
            'path' => $path
        ]);

        if ($ok) {
            $validated['header_image'] = $path;
        }
    }

    // 2) Si envían string → es una imagen de galería
    if (isset($validated['header_image']) && is_string($validated['header_image'])) {
        $validated['header_image'] = $validated['header_image'];
    }

    // 3) Si NO envían nada → mantener la que ya tenía
    if (!isset($validated['header_image'])) {
        $validated['header_image'] = $surprise->header_image;
    }

    // Actualizar sorpresa
    $surprise->update($validated);

    // Auditoría
    AuditService::log(
        'surprise_updated',
        'Surprise',
        $surprise->id,
        $old,
        $surprise->getChanges()
    );

    // Notificación si cambia deadline
    if (isset($validated['deadline']) && $surprise->genius_id) {
        NotificationEvents::deadlineUpdated($surprise);
    }

    return response()->json([
        'success' => true,
        'message' => 'Surprise updated successfully',
        'data' => $surprise
    ]);
}
    public function start(Request $request, $id)
{
    $surprise = Surprise::findOrFail($id);
    $user = $request->user();

    // Solo el GENIUS asignado puede confirmar inicio
    if ($user->id !== $surprise->genius_id) {
        return response()->json([
            'error' => 'Only the assigned genius can confirm the start'
        ], 403);
    }

    // La sorpresa debe estar ya en progreso
    if ($surprise->status !== 'in_progress') {
        return response()->json([
            'error' => 'This surprise is not in progress'
        ], 400);
    }

    // Si ya está marcada como iniciada, no duplicar
    if ($surprise->started_at) {
        return response()->json([
            'success' => true,
            'message' => 'Work already confirmed',
            'data' => $surprise
        ]);
    }

    // Marcar inicio real del trabajo
    $surprise->started_at = now();
    $surprise->save();

    // Auditoría
    AuditService::log(
        'surprise_work_started',
        'Surprise',
        $surprise->id,
        null,
        ['started_at' => $surprise->started_at]
    );

    // Notificación al creador
    NotificationEvents::surpriseStarted($surprise);

    return response()->json([
        'success' => true,
        'message' => 'Work on the surprise has been confirmed',
        'data' => $surprise
    ]);
}
    // Genius entrega la sorpresa
    public function deliver(Request $request, $id)
{
    $surprise = Surprise::findOrFail($id);
    $user = $request->user();

    // Solo el GENIUS asignado puede entregar
    if ($user->id !== $surprise->genius_id) {
        return response()->json([
            'error' => 'Only the assigned genius can deliver the surprise'
        ], 403);
    }

    // La sorpresa debe estar en progreso
    if ($surprise->status !== 'in_progress') {
        return response()->json([
            'error' => 'Only in-progress surprises can be delivered'
        ], 400);
    }

    // Marcar entrega
    $surprise->delivered_at = now();
    $surprise->status = 'delivered';

    // Calcular horas trabajadas
    if ($surprise->started_at) {
        $surprise->hours_spent = $surprise->delivered_at->diffInHours($surprise->started_at);
    }

    $surprise->save();

    // Auditoría
    AuditService::log(
        'surprise_delivered',
        'Surprise',
        $surprise->id,
        ['status' => 'in_progress'],
        [
            'status' => 'delivered',
            'delivered_at' => $surprise->delivered_at,
            'hours_spent' => $surprise->hours_spent
        ]
    );

    // Notificación al creador
    NotificationEvents::surpriseDelivered($surprise);

    return response()->json([
        'success' => true,
        'message' => 'Surprise delivered successfully',
        'data' => $surprise
    ]);
}
    // Creador completa la sorpresa
    public function complete(Request $request, $id)
{
    $surprise = Surprise::with('genius')->findOrFail($id);
    $user = $request->user();

    // Solo el creador puede completar
    if ($user->id !== $surprise->creator_id) {
        return response()->json(['error' => 'Only the creator can complete the surprise'], 403);
    }

    // Solo se puede completar una sorpresa entregada
    if ($surprise->status !== 'delivered') {
        return response()->json(['error' => 'Only delivered surprises can be completed'], 400);
    }

    $completedAt = now();

    $surprise->update([
        'status' => 'completed',
        'completed_at' => $completedAt,
    ]);

    AuditService::log(
        'surprise_completed',
        'Surprise',
        $surprise->id,
        [
            'status' => 'delivered',
            'completed_at' => null,
        ],
        [
            'status' => 'completed',
            'completed_at' => $completedAt,
        ]
    );

    // Activar skill + XP
    $this->activateSkill($surprise);
    $this->addExperience($surprise);

    NotificationEvents::surpriseCompleted($surprise);

    return response()->json([
        'success' => true,
        'message' => 'Surprise completed and skills updated',
        'data' => $surprise->fresh()
    ]);
}
    public function cancel(Request $request, $id)
    {
        $surprise = Surprise::findOrFail($id);
        $user = $request->user();

        // QUIÉN CANCELA
        if ($user->id === $surprise->creator_id) {
            $cancelledBy = 'creator';
        } elseif ($user->id === $surprise->genius_id) {
            $cancelledBy = 'genius';
        } elseif ($user->is_admin) {
            $cancelledBy = 'admin';
        } else {
            return response()->json(['error' => 'You cannot cancel this surprise'], 403);
        }

        // VALIDAR ESTADO
        if (!in_array($surprise->status, ['open', 'in_progress'])) {
            return response()->json(['error' => 'This surprise cannot be cancelled'], 400);
        }

        // VALIDAR MOTIVO
        $request->validate([
            'reason_key' => 'required|in:illness,personal_issue,force_majeure,technical_issue,no_time,uncomfortable,cant_now,no_reason',
            'reason_text' => 'nullable|string|max:1000',
        ]);

        $reasonKey = $request->reason_key;

        // CLASIFICAR MOTIVO
        $reasonType = \App\Services\CancellationReasonService::classify($reasonKey);

        // REGISTRAR CANCELACIÓN
        \App\Models\Cancellation::create([
            'surprise_id' => $surprise->id,
            'genius_id' => $surprise->genius_id,
            'creator_id' => $surprise->creator_id,
            'cancelled_by' => $cancelledBy,
            'reason_key' => $reasonKey,
            'reason_text' => $request->reason_text,
        ]);

        // CAMBIAR ESTADO
        $surprise->status = 'cancelled';
        $surprise->save();

        // PENALIZACIÓN SOLO SI CANCELA EL GENIO
        if ($cancelledBy === 'genius') {
            \App\Services\PenaltyService::applyCancellationPenalty(
                $surprise->genius_id,
                $reasonType,
                $reasonKey
            );
        }

        // AUDITORÍA
        AuditService::log(
            'surprise_cancelled',
            'Surprise',
            $surprise->id,
            ['status' => $surprise->status],
            ['status' => 'cancelled']
        );

        // NOTIFICACIÓN
        NotificationEvents::surpriseCancelled($surprise);

        return response()->json([
            'success' => true,
            'message' => 'Surprise cancelled',
            'reason_type' => $reasonType,
            'data' => $surprise
        ]);
    }
    // Eliminar una sorpresa
 public function destroy($id)
{
    $surprise = Surprise::findOrFail($id);

    // Solo se puede borrar si está en estado open
    if ($surprise->status !== 'open') {
        return response()->json([
            'error' => 'Solo es posible eliminar sorpresas en estado Open'
        ], 400);
    }

    $surprise->delete();

    return response()->json([
        'success' => true,
        'message' => 'Sorpresa eliminada con exito'
    ]);
}

    // Añadir archivos a una sorpresa
    /*public function addFile(Request $request, $id)
{
    $surprise = Surprise::findOrFail($id);
    $user = $request->user();

    // 1. Validación de permisos
    if ($user->id !== $surprise->creator_id && $user->id !== $surprise->genius_id) {
        return response()->json(['error' => 'No autorizado'], 403);
    }

    // 2. Validación de estado
    if (!in_array($surprise->status, ['in_progress', 'delivered'])) {
        return response()->json(['error' => 'No se pueden subir archivos en este estado'], 400);
    }

    // 3. Validación básica
    $request->validate([
        'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,mp4,mp3'
    ]);

    // 4. Validación MIME real
    $allowedMime = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
        'video/mp4',
        'audio/mpeg'
    ];

    if (!in_array($request->file('file')->getMimeType(), $allowedMime)) {
        return response()->json(['error' => 'Tipo de archivo no permitido'], 400);
    }

    // 5. Guardar archivo
    $path = $request->file('file')->store("surprises/$id", 'public');
    $url = asset("storage/" . $path);

    $mime = $request->file('file')->getMimeType();
    $type = explode('/', $mime)[0];

    $file = SurpriseFile::create([
        'surprise_id' => $id,
        'filename' => $request->file('file')->getClientOriginalName(),
        'path' => $path,
        'mime' => $mime,
        'size' => $request->file('file')->getSize(),
        'file_url' => $url,
        'file_type' => $type,
    ]);

    // 6. Notificaciones
    if ($user->id === $surprise->genius_id) {
        \App\Services\NotificationEvents::fileUploadedByGenius($surprise);
    }

    if ($user->id === $surprise->creator_id) {
        \App\Services\NotificationEvents::fileUploadedByCreator($surprise);
    }

    return response()->json([
        'success' => true,
        'message' => 'File added successfully',
        'data' => $file
    ]);
}*/

    // Listar sorpresas creadas por un usuario
public function byCreator($userId)
{
    $surprises = Surprise::where('creator_id', $userId)
        ->with(['files', 'review', 'skill', 'offers'])
        ->withCount('ads')
        ->get()
        ->map(function ($surprise) {

            $xp = 10; // base XP

            // tamaño
            if ($surprise->size === 'MEDIUM') $xp += 10;
            if ($surprise->size === 'LARGE') $xp += 20;
            if ($surprise->size === 'PREMIUM') $xp += 40;

            // urgencia
            if ($surprise->is_urgent) $xp += 15;

            // ads (bonus marketplace)
            if ($surprise->ads_count > 0) $xp += 10;

            // bonus adicional por estar destacada (evita duplicado si quieres ajustarlo luego)
            if ($surprise->ads_count > 0) $xp += 5;

            $surprise->xp_value = $xp;

            return $surprise;
        });

    return response()->json([
        'success' => true,
        'data' => $surprises
    ]);
}

    // Listar sorpresas donde el usuario es genius
    public function byGenius($userId)
    {
        $surprises = Surprise::where('genius_id', $userId)
            ->with(['files', 'review', 'skill'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $surprises
        ]);
    }
    private function activateSkill(Surprise $surprise)
    {
        $genius = $surprise->genius;
        $skillId = $surprise->skill_id;

        if (!$genius || !$skillId) {
            return;
        }

        // Si ya está activa, no hacemos nada
        if ($genius->activeSkills()->where('skill_id', $skillId)->exists()) {
            return;
        }

        // Activar skill con nivel 1 y XP 0
        $genius->activeSkills()->attach($skillId, [
            'level' => 1,
            'xp' => 0,
        ]);
    }

    private function addExperience(Surprise $surprise)
    {
        $genius = $surprise->genius;
        $skillId = $surprise->skill_id;

        if (!$genius || !$skillId) {
            return;
        }

        $skill = $genius->activeSkills()->where('skill_id', $skillId)->first();

        if (!$skill) {
            return;
        }

        $pivot = $skill->pivot;

        // ⭐ Guarda el nivel ANTES de sumar XP
        $oldLevel = $pivot->level;

        $xp = 10; // base

        // Tamaño
        if ($surprise->size === 'MEDIUM') $xp += 10;
        if ($surprise->size === 'LARGE') $xp += 20;
        if ($surprise->size === 'PREMIUM') $xp += 40;

        // Urgente
        if ($surprise->is_urgent) $xp += 15;

        // Valoración
        if (!is_null($surprise->rating_for_genius)) {
            if ($surprise->rating_for_genius >= 4) $xp += 10;
            if ($surprise->rating_for_genius == 5) $xp += 20;
            if ($surprise->rating_for_genius <= 2) $xp = 0; // mala valoración → no gana XP
        }

        $pivot->xp += $xp;

        // Subir nivel cada 100 XP
        while ($pivot->xp >= 100) {
            $pivot->level++;
            $pivot->xp -= 100;
        }

        // ⭐ Notificación SOLO si subió de nivel
        if ($pivot->level > $oldLevel) {
            NotificationEvents::skillLevelUp(
                $genius,
                \App\Models\Skill::find($skillId),
                $pivot->level
            );
        }

        $pivot->save();
    }
}
