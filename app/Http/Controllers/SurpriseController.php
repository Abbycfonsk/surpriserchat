<?php

namespace App\Http\Controllers;

use App\Models\Surprise;
use App\Models\SurpriseFile;
use Illuminate\Http\Request;
use App\Helpers\Notify;

class SurpriseController extends Controller
{
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
            'skill_id' => 'required|exists:skills,id'
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

        $validated = $request->validate([
            'genius_id' => 'nullable|exists:users,id',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'price' => 'nullable|numeric',
            'deadline' => 'nullable|date',
        ]);

        $surprise->update($validated);

        // Si se asigna un genius
        if (isset($validated['genius_id'])) {

            // 🔔 Notificación: nueva sorpresa asignada
            Notify::send(
                $validated['genius_id'],
                'Nueva sorpresa asignada',
                'Te han asignado una nueva sorpresa.',
                'info'
            );

            $genius = $surprise->genius;

            if ($genius && !$genius->skills()->where('skill_id', $surprise->skill_id)->exists()) {
                $genius->skills()->attach($surprise->skill_id);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Surprise updated successfully',
            'data' => $surprise
        ]);
    }

    // Genius acepta la sorpresa
    public function accept($id)
    {
        $surprise = Surprise::findOrFail($id);

        if ($surprise->status !== 'open') {
            return response()->json(['error' => 'Surprise is not open'], 400);
        }

        if (!$surprise->genius_id) {
            return response()->json(['error' => 'No genius assigned'], 400);
        }

        $surprise->status = 'in_progress';
        $surprise->save();

        // 🔔 Notificación: el genius aceptó la sorpresa
        Notify::send(
            $surprise->creator_id,
            'Sorpresa aceptada',
            'El genius ha aceptado tu sorpresa.',
            'success'
        );

        return response()->json([
            'success' => true,
            'message' => 'Surprise accepted and now in progress',
            'data' => $surprise
        ]);
    }

    public function start($id)
    {
        $surprise = Surprise::findOrFail($id);

        if ($surprise->status !== 'in_progress') {
            return response()->json(['error' => 'Surprise is not in progress'], 400);
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
        $surprise->save();

        // 🔔 Notificación: sorpresa completada
        Notify::send(
            $surprise->genius_id,
            'Sorpresa completada',
            'El creador ha marcado la sorpresa como completada.',
            'success'
        );

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

    // Añadir archivos a una sorpresa (versión antigua)
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
