<?php

namespace App\Filament\Resources\TranscriptResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;

class CreateTranscript extends CreateRecord
{
    protected static string $resource = TranscriptResource::class;

    // Default create action is sufficient as audio is submitted via AudioUploadField


}
