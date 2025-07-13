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
                                "<div class='space-y-2'>"
                                ."<div class='flex justify-between items-center'>"
                                ."<div class='flex items-center gap-x-3'>"
                                ."<span class='size-8 flex justify-center items-center border border-gray-200 text-gray-500 rounded-lg dark:border-neutral-700 dark:text-neutral-500'>"
                                ."<svg class='shrink-0 size-5' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'>"
                                ."<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12H9m12-9H3v18h18V3z'/></svg>"
                                ."</span>"
                                ."<div>"
                                ."<p class='text-sm font-medium text-gray-800 dark:text-white' x-text=\"uploadFileName\"></p>"
                                ."<p class='text-xs text-gray-500 dark:text-neutral-500' x-text=\"uploadFileSize\"></p>"
                                ."</div>"
                                ."</div>"
                                ."</div>"
                                ."<div class='flex items-center gap-x-3 whitespace-nowrap'>"
                                ."<div class='flex w-full h-2 bg-gray-200 rounded-full overflow-hidden dark:bg-neutral-700'>"
                                ."<div class='flex flex-col justify-center rounded-full overflow-hidden bg-teal-500 text-xs text-white text-center whitespace-nowrap transition duration-500' x-bind:style=\"`width: \${uploadProgress}%`\"></div>"
                                ."</div>"
                                ."<div class='w-6 text-end'>"
                                ."<span class='text-sm text-gray-800 dark:text-white' x-text=\"`\${uploadProgress}%`\"></span>"
                                ."</div>"
                                ."</div>"
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
