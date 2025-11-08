<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['userOne', 'userTwo'])
            ->get();

        return response()->json($conversations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $authId = Auth::id();

        // Verificar si ya existe una conversación entre los dos usuarios
        $existing = Conversation::where(function ($query) use ($authId, $validated) {
            $query->where('user_one_id', $authId)
                ->where('user_two_id', $validated['user_id']);
        })->orWhere(function ($query) use ($authId, $validated) {
            $query->where('user_one_id', $validated['user_id'])
                ->where('user_two_id', $authId);
        })->first();

        if ($existing) {
            return response()->json($existing);
        }

        // Si no existe, crear una nueva
        $conversation = Conversation::create([
            'user_one_id' => $authId,
            'user_two_id' => $validated['user_id'],
        ]);

        return response()->json($conversation);
    }
    public function destroy(Conversation $conversation)
    {
        // Elimina las imágenes asociadas
        foreach ($conversation->messages as $message) {
            if ($message->image) {
                Storage::disk('public')->delete($message->image);
            }
        }

        // Elimina los mensajes
        $conversation->messages()->delete();

        // Elimina la conversación
        $conversation->delete();

        return response()->json(['message' => 'Conversación eliminada con sus imágenes']);
    }
}
