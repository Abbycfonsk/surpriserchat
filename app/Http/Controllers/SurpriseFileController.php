<?php

namespace App\Http\Controllers;

use App\Models\Surprise;
use App\Models\SurpriseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationEvents;
use App\Services\AuditService;

class SurpriseFileController extends Controller
{
    public function index($id)
    {
        $files = SurpriseFile::where('surprise_id', $id)->get();

        return response()->json([
            'success' => true,
            'data' => $files
        ]);
    }

    public function download($id)
{
    // ⭐ 1. Autenticación manual por token en query string
    if (request()->has('token')) {
        $token = \Laravel\Sanctum\PersonalAccessToken::findToken(request('token'));

        if ($token) {
            \Illuminate\Support\Facades\Auth::login($token->tokenable);
        }
    }

    // ⭐ 2. Obtener usuario autenticado
    $userId = \Illuminate\Support\Facades\Auth::id();
    if (!$userId) {
        return response()->json(['error' => 'Not authorized'], 403);
    }

    // ⭐ 3. Buscar archivo + sorpresa
    $file = \App\Models\SurpriseFile::with('surprise')->findOrFail($id);
    $surprise = $file->surprise;

    // ⭐ 4. Validar permisos (solo creator o genius)
    if (!in_array($userId, [
        $surprise->creator_id,
        $surprise->genius_id
    ])) {
        return response()->json(['error' => 'Not authorized'], 403);
    }

    // ⭐ 5. Validar existencia física del archivo
    $fullPath = storage_path('app/public/' . $file->path);

    if (!file_exists($fullPath)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    // ⭐ 6. Auditoría
    \App\Services\AuditService::log(
        'file_downloaded',
        'SurpriseFile',
        $file->id,
        null,
        ['file' => $file->path]
    );

    // ⭐ 7. Descargar archivo con MIME real
    return response()->download(
        $fullPath,
        $file->filename,
        [
            'Content-Type' => $file->mime,
            'Content-Disposition' => 'attachment; filename="' . $file->filename . '"'
        ]
    );
}
    public function store(Request $request, $id)
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
        AuditService::log(
            'file_uploaded',
            'SurpriseFile',
            $file->id,
            null,
            $file->toArray()
        );
        // 6. Notificaciones
        if ($user->id === $surprise->genius_id) {
            NotificationEvents::fileUploadedByGenius($surprise);
        }

        if ($user->id === $surprise->creator_id) {
            NotificationEvents::fileUploadedByCreator($surprise);
        }

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => $file
        ]);
    }
}
