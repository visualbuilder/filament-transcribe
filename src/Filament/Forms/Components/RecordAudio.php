<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class RecordAudio extends Field
{
    protected string $view = 'filament-transcribe::components.record_audio_field';

    protected string $deviceField = 'recording_device';

    public static function make(string $name = 'recording'): static
    {
        return parent::make($name);
    }

    public function deviceField(string $name): static
    {
        $this->deviceField = $name;

        return $this;
    }

    public function getDeviceField(): string
    {
        return $this->deviceField;
    }

    public function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'deviceField' => $this->getDeviceField(),
        ]);
    }
}
