<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Services\SanitizerService;

class MessageController extends Controller
{
    public function index($conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,pdf|max:10240',
        ]);

        // Debe enviar texto o imagen
        if (!$request->filled('content') && !$request->hasFile('image')) {
            return response()->json([
                'message' => 'Debes enviar texto o una imagen.',
            ], 422);
        }

        // ⭐ Sanitizar SOLO texto libre
        if (isset($validated['content'])) {
            $validated['content'] = SanitizerService::clean($validated['content']);
        }

        // Guardar archivo si existe
        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store("messages", 'public');
        }

        // Crear mensaje
        $message = Message::create([
            'conversation_id' => $validated['conversation_id'],
            'sender_id' => Auth::id(),
            'content' => $validated['content'] ?? null,
            'image' => $path,
        ]);

        return response()->json($message);
    }
}
