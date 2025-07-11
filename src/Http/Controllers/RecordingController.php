<?php

namespace Visualbuilder\FilamentTranscribe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Visualbuilder\FilamentTranscribe\Enums\TranscriptStatus;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;
use Visualbuilder\FilamentTranscribe\Models\Transcript;

class RecordingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'audio' => 'required|file',
        ]);

        $disk = config('filament-transcribe.recordings.disk', config('filesystems.default'));
        $directory = config('filament-transcribe.recordings.directory', 'recordings');

        $path = $request->file('audio')->store($directory, $disk);

        $transcript = Transcript::create([
            'status' => TranscriptStatus::PENDING,
            'redact_pii' => true,
        ]);

        $transcript
            ->addMedia(Storage::disk($disk)->path($path))
            ->usingFileName(basename($path))
            ->toMediaCollection('audio');

        return response()->json([
            'redirect' => TranscriptResource::getUrl('edit', ['record' => $transcript]),
        ]);
    }
}
