<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Fields;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Support\HtmlString;

class AudioUploadField
{
    public static function make() : SpatieMediaLibraryFileUpload
    {
        $allowedFileTypes = config('filament-transcribe.allowed_file_types');

        return SpatieMediaLibraryFileUpload::make('audio')
            ->collection('audio')
            ->disk(config('filament-transcribe.aws.transcribe.inputDisk', 's3'))
            ->visibility('private')
            ->required()
            ->hiddenOn('edit')
            ->maxSize(config('filament-transcribe.max_audio_file_size_kb', 128000))
            ->acceptedFileTypes(array_keys($allowedFileTypes))
            ->placeholder(new HtmlString(__('vb-transcribe::fields.audio.placeholder')))
            ->helperText(__('vb-transcribe::fields.audio.helper_text', [
                'types' => strtoupper(implode(', ', array_unique($allowedFileTypes))),
                'size'  => round(config('filament-transcribe.max_audio_file_size_kb', 128000) / 1024),
            ]));
    }
}
