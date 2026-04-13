<?php

namespace App\Http\Controllers;

use App\Models\Surprise;
use App\Models\SurpriseFile;
use Illuminate\Http\Request;
use App\Helpers\Notify;
use App\Services\GeniusLevelService;

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
        Notify::send(
            $surprise->creator_id,
            'Sorpresa creada',
            'Tu sorpresa ha sido creada correctamente.',
            'success'
        );

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
            Notify::send(
                $surprise->genius_id,
                'Deadline actualizado',
                'El creador ha actualizado la fecha límite de la sorpresa.',
                'info'
            );
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
        Notify::send(
            $surprise->creator_id,
            'Sorpresa entregada',
            'El genius ha entregado tu sorpresa.',
            'success'
        );

        return response()->json([
            'success' => true,
            'message' => 'Surprise delivered',
            'data' => $surprise
        ]);
    }

    // Creador completa la sorpresa
    public function complete($id)
    {
        $surprise = Surprise::findOrFail($id);

        if ($surprise->status !== 'delivered') {
            return response()->json(['error' => 'Surprise is not delivered'], 400);
        }

        $surprise->status = 'completed';
        $surprise->completed_at = now();
        $surprise->save();

        // 🔔 Notificación: sorpresa completada
        Notify::send(
            $surprise->genius_id,
            'Sorpresa completada',
            'El creador ha marcado la sorpresa como completada.',
            'success'
        );

        // ⭐⭐⭐ LÓGICA DE PUNTOS Y NIVELES ⭐⭐⭐

        $genius = $surprise->genius;

        // +5 por completar
        $this->geniusLevelService->addPoints($genius, 5, 'COMPLETE', $surprise);

        // rating
        if ($surprise->rating_for_genius == 5) {
            $this->geniusLevelService->addPoints($genius, 3, 'RATING_5', $surprise);
        } elseif ($surprise->rating_for_genius == 4) {
            $this->geniusLevelService->addPoints($genius, 1, 'RATING_4', $surprise);
        }

        // entrega antes de tiempo
        if ($surprise->deadline && $surprise->completed_at < $surprise->deadline) {
            $this->geniusLevelService->addPoints($genius, 2, 'EARLY_DELIVERY', $surprise);
        }

        return response()->json([
            'success' => true,
            'message' => 'Surprise completed successfully',
            'data' => $surprise
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
}
