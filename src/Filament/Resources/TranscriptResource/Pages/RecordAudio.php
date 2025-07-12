<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;
use Livewire\WithFileUploads;
use Visualbuilder\FilamentTranscribe\Filament\Forms\Components\RecordingInfo;
use Visualbuilder\FilamentTranscribe\Filament\Forms\Components\SoundCheck;

class RecordAudio extends CreateRecord
{
    use WithFileUploads;
    protected static string $resource = TranscriptResource::class;

    protected static string $view = 'filament-transcribe::pages.record-audio';

    public ?array $data = [];
    /**
     * Temporary uploaded audio file.
     */
    public $recordingFile;

    /**
     * Whether the user is currently recording.
     */
    public bool $recording = false;

    /**
     * Whether the user is checking microphone levels.
     */
    public bool $checkingLevels = false;

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
                    ->required()
                    ->visible(fn($livewire) => ! $livewire->recording && ! $livewire->checkingLevels),

                Toggle::make('redact_pii')
                    ->default(true)
                    ->label('Redact Personally Identifiable Information')
                    ->visible(fn($livewire) => ! $livewire->recording && ! $livewire->checkingLevels),

                SoundCheck::make(),
                RecordingInfo::make(),
            ])
            ->statePath('data');
    }

    public function create(bool $another = false): void
    {
        if (! $this->recordingFile) {
            return;
        }

        $disk = config('filament-transcribe.recordings.disk');
        $dir  = trim(config('filament-transcribe.recordings.directory'), '/');
        $name = 'recording-' . now()->format('YmdHis') . '.webm';
        $path = $this->recordingFile->storeAs($dir, $name, $disk);
        $this->recordingFile = null;
        $this->recording = false;
        $this->checkingLevels = false;

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
