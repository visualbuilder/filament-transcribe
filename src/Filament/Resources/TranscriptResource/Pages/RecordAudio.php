<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;
use Livewire\WithFileUploads;

class RecordAudio extends CreateRecord
{
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
                Toggle::make('redact_pii')
                    ->default(true)
                    ->label('Redact Personally Identifiable Information'),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        if (! $this->recording) {
            return;
        }

        $disk = config('filament-transcribe.recordings.disk');
        $dir  = trim(config('filament-transcribe.recordings.directory'), '/');
        $name = 'recording-' . now()->format('YmdHis') . '.webm';
        $path = $this->recording->storeAs($dir, $name, $disk);

        $model = TranscriptResource::getModel();
        $transcript = $model::create([
            'redact_pii' => $this->data['redact_pii'] ?? true,
        ]);

        $transcript->addMedia(Storage::disk($disk)->path($path))
            ->usingFileName($name)
            ->toMediaCollection('audio');

        Storage::disk($disk)->delete($path);

        $this->redirect(TranscriptResource::getUrl('edit', ['record' => $transcript]));
    }
}
