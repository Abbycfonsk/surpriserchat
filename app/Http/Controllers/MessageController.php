<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\Conversation;
use App\Services\SanitizerService;
use App\Services\NotificationEvents;
use App\Services\AuditService;
use App\Services\FileSecurityService;

class MessageController extends Controller
{
    // Listar mensajes de una conversación
    public function index($conversationId)
    {
        $userId = Auth::id();

        $conversation = Conversation::with('surprise')->findOrFail($conversationId);

        // Solo creador o genius pueden ver
        if (!in_array($userId, [$conversation->creator_id, $conversation->genius_id])) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    // Enviar mensaje a una conversación existente
    public function store(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,pdf|max:10240',
        ]);

        $userId = Auth::id();
        $conversation = Conversation::with('surprise')->findOrFail($validated['conversation_id']);
        $surprise = $conversation->surprise;

        // Solo creador o genius
        if (!in_array($userId, [$conversation->creator_id, $conversation->genius_id])) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        // Estados permitidos: open, in_progress, delivered, completed
        if (!in_array($surprise->status, ['open', 'in_progress', 'delivered', 'completed'])) {
            return response()->json(['error' => 'Messages are not allowed in this surprise status'], 400);
        }

        // Debe enviar texto o imagen
        if (!$request->filled('content') && !$request->hasFile('image')) {
            return response()->json([
                'message' => 'Debes enviar texto o una imagen.',
            ], 422);
        }

        // Límite de longitud
        if ($request->filled('content') && mb_strlen($request->input('content')) > 2000) {
            return response()->json([
                'message' => 'El mensaje es demasiado largo (máx 2000 caracteres).',
            ], 422);
        }

        // Anti-flood: 1 mensaje cada 2 segundos por usuario en esa conversación
        $lastMessage = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastMessage && $lastMessage->created_at->gt(now()->subSeconds(2))) {
            return response()->json([
                'message' => 'Estás enviando mensajes demasiado rápido.',
            ], 429);
        }

        // Anti-duplicados: mismo contenido en últimos 10s
        if ($request->filled('content')) {
            $duplicate = Message::where('conversation_id', $conversation->id)
                ->where('sender_id', $userId)
                ->where('content', $request->input('content'))
                ->where('created_at', '>=', now()->subSeconds(10))
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'message' => 'Ya has enviado este mismo mensaje hace un momento.',
                ], 400);
            }
        }

        // Sanitizar texto
        $content = $request->filled('content')
            ? SanitizerService::clean($request->input('content'))
            : null;

        // Guardar archivo si existe
        $path = null;

        if ($request->hasFile('image')) {

            $file = $request->file('image');

            // 1. Validar MIME real
            if (!FileSecurityService::validateRealMime($file)) {
                return response()->json([
                    'error' => 'El archivo no coincide con su tipo real. Posible archivo malicioso.'
                ], 400);
            }

            // 2. Sanitizar nombre
            $safeName = FileSecurityService::sanitizeFilename($file->getClientOriginalName());

            // 3. Guardar con nombre seguro
            $path = $file->storeAs("messages", $safeName, 'public');
        }

        // Crear mensaje
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'content' => $content,
            'image' => $path,
        ]);

        // Auditoría
        AuditService::log(
            'message_sent',
            'Message',
            $message->id,
            null,
            $message->toArray()
        );

        // Notificación al otro usuario
        NotificationEvents::messageSent($conversation, $message);

        return response()->json($message);
    }


    public function download($id)
    {
        $userId = Auth::id();
        $message = Message::with('conversation')->findOrFail($id);

        // Permisos
        if (!in_array($userId, [
            $message->conversation->creator_id,
            $message->conversation->genius_id
        ])) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        if (!$message->image) {
            return response()->json(['error' => 'No file'], 404);
        }

        // Auditoría
        AuditService::log(
            'file_downloaded',
            'Message',
            $message->id,
            null,
            ['file' => $message->image]
        );

        return response()->download(storage_path("app/public/" . $message->image));
    }
    // Enviar mensaje por sorpresa (auto-crea conversación si no existe)
    public function storeBySurprise(Request $request, $surpriseId)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'content' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,pdf|max:10240',
        ]);

        if (!$request->filled('content') && !$request->hasFile('image')) {
            return response()->json([
                'message' => 'Debes enviar texto o una imagen.',
            ], 422);
        }

        $surprise = \App\Models\Surprise::findOrFail($surpriseId);

        // Solo creador o genius asignado
        if (!in_array($userId, [$surprise->creator_id, $surprise->genius_id])) {
            return response()->json(['error' => 'Not authorized for this surprise'], 403);
        }

        if (!$surprise->genius_id) {
            return response()->json(['error' => 'No genius assigned to this surprise'], 400);
        }

        if (!in_array($surprise->status, ['open', 'in_progress', 'delivered', 'completed'])) {
            return response()->json(['error' => 'Messages are not allowed in this surprise status'], 400);
        }

        // Buscar o crear conversación
        $conversation = Conversation::firstOrCreate(
            [
                'surprise_id' => $surprise->id,
                'creator_id' => $surprise->creator_id,
                'genius_id' => $surprise->genius_id,
            ]
        );

        // Reusar lógica de store: montamos un Request fake con conversation_id
        $request->merge(['conversation_id' => $conversation->id]);

        return $this->store($request);
    }
}
