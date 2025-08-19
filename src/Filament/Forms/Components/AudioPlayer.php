<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Forms\Components;

use Filament\Forms\Components\ViewField;

class AudioPlayer extends ViewField
{
    protected string $view = 'filament-transcribe::components.audio_player';

    public static function make(?string $name = 'audioPlayer'): static
    {
        return parent::make($name);
    }
}
