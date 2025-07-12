<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\WithFileUploads;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;

class RecordAudio extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;
    protected static string $resource = TranscriptResource::class;

    protected static string $view = 'filament-transcribe::pages.record-audio';

    public ?array $data = [];
    public $recording;

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

    public function create(): void
    {
        if ($this->recording) {
            $disk = config('filament-transcribe.recordings.disk');
            $dir = trim(config('filament-transcribe.recordings.directory'), '/');
            $name = 'recording-' . now()->format('YmdHis') . '.webm';
            $path = $this->recording->storeAs($dir, $name, $disk);
            // Placeholder for saving other form data
            logger()->info('Stored recording', ['path' => $path]);
        }
        dd($this->form->getState());
    }
}
