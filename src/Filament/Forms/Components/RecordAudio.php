<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class RecordAudio extends Field
{
    protected string $view = 'filament-transcribe::components.record_audio_field';

    public static function make(string $name = 'recording'): static
    {
        return parent::make($name);
    }
}
