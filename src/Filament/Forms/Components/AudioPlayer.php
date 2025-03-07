<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Forms\Components;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\View;

class AudioPlayer extends Component
{

    protected string $view = 'filament-transcribe::components.audio_player';

    public static function make(string $name = 'audioPlayer'): static
    {
        return app(static::class);
    }
}
