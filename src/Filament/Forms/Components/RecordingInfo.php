<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Forms\Components;


use Filament\Schemas\Components\Component;

class RecordingInfo extends Component
{
    protected string $view = 'filament-transcribe::components.recording_info';

    public static function make(string $name = 'recordingInfo'): static
    {
        return app(static::class);
    }
}
