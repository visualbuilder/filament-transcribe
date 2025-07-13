<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Storage;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;
use Livewire\WithFileUploads;
use Visualbuilder\FilamentTranscribe\Filament\Forms\Components\RecordingInfo;
use Visualbuilder\FilamentTranscribe\Filament\Forms\Components\SoundCheck;
use Illuminate\Support\HtmlString;

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

    /**
     * Whether the uploaded file is currently being sent to the server.
     */
    public bool $showProgress = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Record Session')
                    ->schema([
                        Select::make('device')
                            ->label('Audio Source')
                            ->native()
                            ->options([])
                            ->required()
                            ->visible(fn($livewire) => ! $livewire->recording && ! $livewire->checkingLevels && ! $livewire->showProgress),
                        Toggle::make('redact_pii')
                            ->default(true)
                            ->label('Redact Personally Identifiable Information')
                            ->visible(fn($livewire) => ! $livewire->recording && ! $livewire->checkingLevels && ! $livewire->showProgress),
                        SoundCheck::make(),
                        RecordingInfo::make(),
                        Placeholder::make('progress')
                            ->label(false)
                            ->content(new HtmlString(
                                "<div class='flex flex-col items-center justify-center min-h-[100px] space-y-2'>"
                                ."<div class='block-loader'></div>"
                                ."<progress max='100' class='w-full' x-bind:value=\"uploadProgress\"></progress>"
                                ."<span>Uploading your audio file</span>"
                                ."</div>"
                            ))
                            ->visible(fn($livewire) => $livewire->showProgress),
                    ])
                    ->footerActions([
                        Action::make('check_levels')
                            ->label(__('vb-transcribe::audio_recorder.buttons.check_levels'))
                            ->visible(fn($livewire) => ! $livewire->recording && ! $livewire->checkingLevels && ! $livewire->showProgress)
                            ->action('startLevelCheck'),
                        Action::make('start_recording')
                            ->icon('heroicon-m-microphone')
                            ->label(__('vb-transcribe::audio_recorder.buttons.start_recording'))
                            ->visible(fn($livewire) => $livewire->checkingLevels)
                            ->action('startRecording'),
                        Action::make('stop_recording')
                            ->label(__('vb-transcribe::audio_recorder.buttons.stop'))
                            ->icon('fas-circle-stop')
                            ->visible(fn($livewire) => $livewire->recording)
                            ->action('stopRecording'),
                    ])->footerActionsAlignment(Alignment::Center)
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

    public function startLevelCheck(): void
    {
        $this->checkingLevels = true;
        $this->showProgress = false;
    }

    public function stopLevelCheck(): void
    {
        $this->checkingLevels = false;
        $this->showProgress = false;
    }

    public function startRecording(): void
    {
        $this->recording = true;
        $this->checkingLevels = false;
        $this->showProgress = false;
    }

    public function stopRecording(): void
    {
        $this->recording = false;
        $this->checkingLevels = false;
        $this->showProgress = true;
    }
}
