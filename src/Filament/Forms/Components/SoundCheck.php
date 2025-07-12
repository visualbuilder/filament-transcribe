<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Forms\Components;

use Filament\Forms\Components\Component;

class SoundCheck extends Component
{
    protected string $view = 'filament-transcribe::components.sound_check';

    public static function make(string $name = 'soundCheck'): static
    {
        return app(static::class);
    }
}
