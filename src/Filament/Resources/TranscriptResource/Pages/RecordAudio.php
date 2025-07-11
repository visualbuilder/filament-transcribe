<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;

class RecordAudio extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = TranscriptResource::class;

    protected static string $view = 'filament-transcribe::pages.record-audio';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('device')
                    ->label('Audio Source')
                    ->native()
                    ->options([])
                    ->required(),
            ])
            ->statePath('data');
    }

    public function record(): void
    {
        // Placeholder for form submission logic
        $this->form->getState();
    }
}
