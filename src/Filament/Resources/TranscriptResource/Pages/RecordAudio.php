<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;

class RecordAudio extends Page
{
    protected static string $resource = TranscriptResource::class;

    protected static string $view = 'filament-transcribe::pages.record-audio';

    public ?string $device = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('device')
                ->label('Audio Source')
                ->native()
                ->options([])
                ->required(),
        ]);
    }
}
