<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;
use App\Models\Surprise;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    // Listar conversaciones del usuario autenticado
    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::where('creator_id', $userId)
            ->orWhere('genius_id', $userId)
            ->with(['creator', 'genius', 'surprise'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($conversations);
    }

    // Crear (o devolver) conversación ligada a una sorpresa
    public function store(Request $request)
    {
        $validated = $request->validate([
            'surprise_id' => 'required|exists:surprises,id',
        ]);

        $userId = Auth::id();
        $surprise = Surprise::findOrFail($validated['surprise_id']);

        // Solo creador o genius asignado pueden crear/abrir conversación
        if (!in_array($userId, [$surprise->creator_id, $surprise->genius_id])) {
            return response()->json(['error' => 'Not authorized for this surprise'], 403);
        }

        // Debe haber genius asignado para conversación
        if (!$surprise->genius_id) {
            return response()->json(['error' => 'No genius assigned to this surprise'], 400);
        }

        // Buscar conversación existente
        $conversation = Conversation::where('surprise_id', $surprise->id)
            ->where('creator_id', $surprise->creator_id)
            ->where('genius_id', $surprise->genius_id)
            ->first();

        if ($conversation) {
            return response()->json($conversation);
        }

        // Crear nueva conversación
        $conversation = Conversation::create([
            'surprise_id' => $surprise->id,
            'creator_id' => $surprise->creator_id,
            'genius_id' => $surprise->genius_id,
        ]);

        return response()->json($conversation);
    }

    // Eliminar conversación + mensajes + imágenes
    public function destroy(Conversation $conversation)
    {
        $userId = Auth::id();

        if (!in_array($userId, [$conversation->creator_id, $conversation->genius_id])) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        foreach ($conversation->messages as $message) {
            if ($message->image) {
                Storage::disk('public')->delete($message->image);
            }
        }

        $conversation->messages()->delete();
        $conversation->delete();

        return response()->json(['message' => 'Conversation and messages deleted']);
    }
}
