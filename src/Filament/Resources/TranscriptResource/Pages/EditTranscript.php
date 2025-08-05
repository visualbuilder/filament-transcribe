<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;


use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;
use Symfony\Component\Process\Process;
use Visualbuilder\FilamentTranscribe\Enums\TranscriptStatus;
use Visualbuilder\FilamentTranscribe\Filament\Actions\TranscribeAction;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;
use Visualbuilder\FilamentTranscribe\Jobs\TranscribeAudioJob;

class EditTranscript extends EditRecord
{
    protected static string $resource = TranscriptResource::class;

    public bool $showProgress = false;

    public bool $showTranscribe = false;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->showProgress = in_array($this->record->status, [TranscriptStatus::PENDING, TranscriptStatus::PROCESSING]);
        $this->showTranscribe = in_array($this->record->status, [TranscriptStatus::FAILED]);

        if ($this->record->status==TranscriptStatus::PENDING) {
            //Autostart transcribing
            $this->startJob();
        }


    }

    public function startJob()
    {
        // ffprobe returns "N/A" for WebM audio files. Instead copy the file
        // using ffmpeg and parse the progress output for the last "time=" value
        // which represents the duration.

        $media = $this->record->getFirstMedia('audio');
        if ($media) {
            $tempFile = tempnam(sys_get_temp_dir(), 'dur');
            if ($tempFile !== false) {
                $tempFile .= '.webm';
                try {
                    $process = Process::fromShellCommandline(
                        'ffmpeg -i %s -c copy -map 0 -f webm -y %s 2>&1',
                        [$media->getPath(), $tempFile]
                    );
                    $process->run();

                    $output = $process->getErrorOutput() . $process->getOutput();

                    if (preg_match_all('/time=(\d{2}:\d{2}:\d{2}\.\d+)/', $output, $matches) && $matches[1]) {
                        $time = end($matches[1]);
                        [$h, $m, $s] = sscanf($time, '%d:%d:%f');
                        $duration = (int) round($h * 3600 + $m * 60 + $s);
                        $media->setCustomProperty('duration_seconds', $duration);
                        $media->save();
                    }
                } catch (\Throwable $e) {
                    // Ignore errors reading metadata; duration will be filled later from AWS
                } finally {
                    @unlink($tempFile);
                }
            }
        }



        Notification::make()
            ->title('Audio Submitted for Transcribing')
            ->body('Processing has started. You can wait on this page, or safely close it and return later.  Expect to wait around 15-30 seconds per minute of audio')
            ->success()
            ->send();

        TranscribeAudioJob::dispatch($this->record);
    }

    /**
     * Event Listener sent by broadcast
     *
     * @param $transcript
     *
     * @return void
     */
    #[On('transcriptUpdated')]
    public function onTranscriptUpdated($transcript): void
    {
        // Get the current state to preserve fields like coaching_session_id
        $currentState = $this->form->getState();

        // Merge in the new values for transcribed_html and status
        $this->form->fill(array_merge($currentState, [
            'transcribed_html' => $transcript['transcribed_html'],
            'status'           => $transcript['status'],
        ]));

        if (in_array($transcript['status'], [TranscriptStatus::COMPLETED->value, TranscriptStatus::FAILED->value])) {
            // Hide the spinner
            $this->showProgress = false;
            $this->showTranscribe = false;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            TranscribeAction::make()->visible(fn($livewire) => $livewire->showTranscribe)
        ];
    }

}
