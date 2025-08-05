<?php

namespace VisualBuilder\FilamentTranscribe\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \VisualBuilder\FilamentTranscribe\FilamentTranscribe
 */
class FilamentTranscribe extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \VisualBuilder\FilamentTranscribe\FilamentTranscribe::class;
    }
}
