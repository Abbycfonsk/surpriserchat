<?php

namespace App\Http\Controllers;

use App\Models\Surprise;
use App\Models\SurpriseFile;
use Illuminate\Http\Request;
use App\Helpers\Notify;
use App\Services\GeniusLevelService;
use App\Services\NotificationEvents;

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

        if (!empty($validated['genius_id']) && $validated['creator_id'] == $validated['genius_id']) {
            return response()->json([
                'error' => 'Creator and genius cannot be the same user'
            ], 400);
        }

        // Siempre empieza en open
        $validated['status'] = 'open';

        $surprise = Surprise::create($validated);

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
            // El creador puede modificar casi todo
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
            // El creador solo puede aclarar cosas o extender deadline
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

        // Actualizar sorpresa
        $surprise->update($validated);

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

    public function start($id)
    {
        $surprise = Surprise::findOrFail($id);

        if ($surprise->status !== 'in_progress') {
            return response()->json(['error' => 'Surprise is not in progress'], 400);
        }

        $genius = $surprise->genius;

        // Revalidar límites por seguridad
        if (!in_array($surprise->size, $genius->allowedSurpriseSizes())) {
            return response()->json(['error' => 'You cannot work on this type of surprise'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Work started',
            'data' => $surprise
        ]);
    }

    // Genius entrega la sorpresa
    public function deliver(Request $request, $id)
    {
        $surprise = Surprise::findOrFail($id);

        if ($surprise->status !== 'in_progress') {
            return response()->json(['error' => 'Surprise is not in progress'], 400);
        }

        $genius = $surprise->genius;

        // Revalidar límites por seguridad
        if (!in_array($surprise->size, $genius->allowedSurpriseSizes())) {
            return response()->json(['error' => 'You cannot deliver this type of surprise'], 403);
        }

        $surprise->status = 'delivered';
        $surprise->save();

        // 🔔 Notificación: sorpresa entregada
        NotificationEvents::surpriseDelivered($surprise);

        return response()->json([
            'success' => true,
            'message' => 'Surprise delivered',
            'data' => $surprise
        ]);
    }

    // Creador completa la sorpresa
    public function complete($id)
    {
        $surprise = Surprise::with('genius')->findOrFail($id);

        $surprise->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // 1) Activar skill si no estaba activa
        $this->activateSkill($surprise);

        // 2) Sumar XP y subir nivel si corresponde
        $this->addExperience($surprise);
        NotificationEvents::surpriseCompleted($surprise);

        return response()->json([
            'success' => true,
            'message' => 'Surprise completed and skills updated'
        ]);
    }

    public function cancel($id)
    {
        $surprise = Surprise::findOrFail($id);

        if ($surprise->status === 'completed') {
            return response()->json(['error' => 'Cannot cancel a completed surprise'], 400);
        }

        if ($surprise->status === 'delivered') {
            return response()->json(['error' => 'Cannot cancel a delivered surprise'], 400);
        }

        $surprise->status = 'cancelled';
        $surprise->save();

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
    public function addFile(Request $request, $id)
    {
        $surprise = Surprise::findOrFail($id);

        $validated = $request->validate([
            'file_url' => 'required|string',
            'file_type' => 'required|string',
        ]);

        $file = SurpriseFile::create([
            'surprise_id' => $surprise->id,
            'file_url' => $validated['file_url'],
            'file_type' => $validated['file_type'],
        ]);
        $user = $request->user();

        // Caso 1: el que sube es el GENIUS de esta sorpresa
        if ($user->id === $surprise->genius_id) {
            // Notificamos al CREADOR
            \App\Services\NotificationEvents::fileUploadedByGenius($surprise);
        }

        // Caso 2: el que sube es el CREADOR de esta sorpresa
        if ($user->id === $surprise->creator_id) {
            // Notificamos al GENIUS (si existe)
            \App\Services\NotificationEvents::fileUploadedByCreator($surprise);
        }
        return response()->json([
            'success' => true,
            'message' => 'File added successfully',
            'data' => $file
        ]);
    }

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
