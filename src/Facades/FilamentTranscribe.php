<?php

namespace Visualbuilder\FilamentTranscribe\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Visualbuilder\FilamentTranscribe\FilamentTranscribe
 */
class FilamentTranscribe extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Visualbuilder\FilamentTranscribe\FilamentTranscribe::class;
    }
}
