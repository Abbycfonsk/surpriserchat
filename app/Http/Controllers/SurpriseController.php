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
        $surprises = Surprise::with(['creator', 'genius', 'files', 'reviews'])->get();

        return response()->json([
            'success' => true,
            'data' => $surprises
        ]);
    }

    // Ver una sorpresa por ID
    public function show($id)
    {
        $surprise = Surprise::with(['creator', 'genius', 'files', 'reviews'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $surprise
        ]);
    }

    // Crear una sorpresa
    public function store(Request $request)
    {
        $validated = $request->validate([
            'creator_id' => 'required|exists:users,id',
            'genius_id' => 'nullable|exists:users,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|string|in:open,in_progress,delivered,completed,cancelled',
            'price' => 'nullable|numeric',
            'deadline' => 'nullable|date',
            'skill_id' => 'required|exists:skills,id',
            'size' => 'required|string|in:SMALL,MEDIUM,LARGE,PREMIUM',
            'is_urgent' => 'nullable|boolean'
        ]);

        $validated['title'] = SanitizerService::clean($validated['title']);
        $validated['description'] = SanitizerService::clean($validated['description']);




        // Siempre empieza en open
        $validated['status'] = 'open';

        if (!empty($validated['genius_id']) && $validated['creator_id'] == $validated['genius_id']) {
            return response()->json([
                'error' => 'Creator and genius cannot be the same user'
            ], 400);
        }

        $surprise = Surprise::create($validated);

        AuditService::log(
            'surprise_created',
            'Surprise',
            $surprise->id,
            null,
            $surprise->toArray()
        );
        // 🔔 Notificación: sorpresa creada


        NotificationEvents::surpriseCreated($surprise);

        return response()->json([
            'success' => true,
            'message' => 'Surprise created successfully',
            'data' => $surprise
        ]);
    }

    // Actualizar una sorpresa
    public function update(Request $request, $id)
    {
        $surprise = Surprise::findOrFail($id);
        $user = $request->user();

        $isCreator = $user->id === $surprise->creator_id;
        $isGenius = $user->id === $surprise->genius_id;

        // ❌ El genio NO puede modificar la sorpresa
        if ($isGenius) {
            return response()->json([
                'error' => 'Genius cannot modify surprise details'
            ], 403);
        }

        // ❌ Nadie puede modificar una sorpresa completada
        if ($surprise->status === 'completed') {
            return response()->json([
                'error' => 'Completed surprises cannot be modified'
            ], 400);
        }

        // ❌ Nadie puede modificar una sorpresa entregada
        if ($surprise->status === 'delivered') {
            return response()->json([
                'error' => 'Delivered surprises cannot be modified'
            ], 400);
        }

        // Solo el creador puede modificar
        if (!$isCreator) {
            return response()->json([
                'error' => 'Only the creator can modify the surprise'
            ], 403);
        }

        // VALIDACIÓN SEGÚN ESTADO
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
                'target_lng' => 'nullable|numeric'
            ];
        }

        if ($surprise->status === 'in_progress') {
            $rules = [
                'description' => 'nullable|string',
                'deadline' => 'nullable|date|after_or_equal:today'
            ];
        }

        $validated = $request->validate($rules);

        // ❌ Nunca permitir cambiar el estado manualmente
        unset($validated['status']);

        // ❌ Nunca permitir asignar genio manualmente
        unset($validated['genius_id']);

        // ⭐ SANITIZACIÓN SOLO DE TEXTO LIBRE
        if (isset($validated['title'])) {
            $validated['title'] = SanitizerService::clean($validated['title']);
        }

        if (isset($validated['description'])) {
            $validated['description'] = SanitizerService::clean($validated['description']);
        }

        if (isset($validated['target_name'])) {
            $validated['target_name'] = SanitizerService::clean($validated['target_name']);
        }

        if (isset($validated['target_city'])) {
            $validated['target_city'] = SanitizerService::clean($validated['target_city']);
        }

        if (isset($validated['target_country'])) {
            $validated['target_country'] = SanitizerService::clean($validated['target_country']);
        }

        // Guardamos valores previos para auditoría
        $old = $surprise->getOriginal();

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

        // Notificación opcional si cambia el deadline
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

        // Solo el GENIUS puede iniciar
        if ($user->id !== $surprise->genius_id) {
            return response()->json(['error' => 'Only the assigned genius can start the surprise'], 403);
        }

        // Validación de estado
        if ($surprise->status !== 'open') {
            return response()->json(['error' => 'Only open surprises can be started'], 400);
        }

        // Cambiar estado
        $surprise->status = 'in_progress';
        $surprise->save();
        AuditService::log(
            'surprise_started',
            'Surprise',
            $surprise->id,
            ['status' => 'open'],
            ['status' => 'in_progress']
        );
        NotificationEvents::surpriseStarted($surprise);

        return response()->json([
            'success' => true,
            'message' => 'Surprise started',
            'data' => $surprise
        ]);
    }
    // Genius entrega la sorpresa
    public function deliver(Request $request, $id)
    {
        $surprise = Surprise::findOrFail($id);
        $user = $request->user();

        // Solo el GENIUS puede entregar
        if ($user->id !== $surprise->genius_id) {
            return response()->json(['error' => 'Only the assigned genius can deliver the surprise'], 403);
        }

        // Validación de estado
        if ($surprise->status !== 'in_progress') {
            return response()->json(['error' => 'Only surprises in progress can be delivered'], 400);
        }

        $surprise->status = 'delivered';
        $surprise->save();
        AuditService::log(
            'surprise_delivered',
            'Surprise',
            $surprise->id,
            ['status' => 'in_progress'],
            ['status' => 'delivered']
        );
        NotificationEvents::surpriseDelivered($surprise);

        return response()->json([
            'success' => true,
            'message' => 'Surprise delivered',
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

        // Validación de estado
        if ($surprise->status !== 'delivered') {
            return response()->json(['error' => 'Only delivered surprises can be completed'], 400);
        }

        $surprise->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        AuditService::log(
            'surprise_completed',
            'Surprise',
            $surprise->id,
            ['status' => 'delivered'],
            ['status' => 'completed']
        );
        // Activar skill + XP
        $this->activateSkill($surprise);
        $this->addExperience($surprise);

        NotificationEvents::surpriseCompleted($surprise);

        return response()->json([
            'success' => true,
            'message' => 'Surprise completed and skills updated'
        ]);
    }
    public function cancel(Request $request, $id)
    {
        $surprise = Surprise::findOrFail($id);
        $user = $request->user();

        // Solo el creador puede cancelar
        if ($user->id !== $surprise->creator_id) {
            return response()->json(['error' => 'Only the creator can cancel the surprise'], 403);
        }

        // Validación de estado
        if (!in_array($surprise->status, ['open', 'in_progress'])) {
            return response()->json(['error' => 'This surprise cannot be cancelled'], 400);
        }

        $surprise->status = 'cancelled';
        $surprise->save();
        AuditService::log(
            'surprise_cancelled',
            'Surprise',
            $surprise->id,
            ['status' => $surprise->status],
            ['status' => 'cancelled']
        );
        NotificationEvents::surpriseCancelled($surprise);

        return response()->json([
            'success' => true,
            'message' => 'Surprise cancelled',
            'data' => $surprise
        ]);
    }
    // Eliminar una sorpresa
    public function destroy($id)
    {
        $surprise = Surprise::findOrFail($id);
        $surprise->delete();

        return response()->json([
            'success' => true,
            'message' => 'Surprise deleted successfully'
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
            ->with(['files', 'reviews'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $surprises
        ]);
    }

    // Listar sorpresas donde el usuario es genius
    public function byGenius($userId)
    {
        $surprises = Surprise::where('genius_id', $userId)
            ->with(['files', 'reviews'])
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
