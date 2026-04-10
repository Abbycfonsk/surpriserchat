<?php

namespace App\Http\Controllers;

use App\Models\Surprise;
use App\Models\SurpriseFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Notify;

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
        $file = SurpriseFile::findOrFail($id);

        $fullPath = storage_path('app/public/' . $file->path);

        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($fullPath, $file->filename, [
            'Content-Type' => $file->mime,
            'Content-Disposition' => 'attachment; filename="' . $file->filename . '"'
        ]);
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240' // 10MB
        ]);

        $surprise = Surprise::findOrFail($id);

        // Guardar archivo en storage/app/public/surprises/{id}/
        $path = $request->file('file')->store("surprises/$id", 'public');

        // Crear URL pública
        $url = asset("storage/" . $path);

        // Detectar tipo de archivo (simple)
        $mime = $request->file('file')->getMimeType();
        $type = explode('/', $mime)[0]; // image, video, audio, application...

        $file = SurpriseFile::create([
            'surprise_id' => $id,
            'filename' => $request->file('file')->getClientOriginalName(),
            'path' => $path,
            'mime' => $mime,
            'size' => $request->file('file')->getSize(),
            'file_url' => $url,
            'file_type' => $type,
        ]);
        Notify::info(
            $surprise->creator_id,
            'Nuevo archivo disponible',
            'El genius ha subido un archivo a tu sorpresa.'
        );
        Notify::info(
            $surprise->genius_id,
            'Nuevo archivo del creador',
            'El creador ha subido un archivo a la sorpresa.'
        );

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => $file
        ]);
    }
}
