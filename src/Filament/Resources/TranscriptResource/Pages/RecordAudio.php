<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;

use Filament\Resources\Pages\Page;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;

class RecordAudio extends Page
{
    protected static string $resource = TranscriptResource::class;

    protected static string $view = 'filament-transcribe::pages.record-audio';
}
