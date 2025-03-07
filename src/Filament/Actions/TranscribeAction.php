<?php

namespace VisualBuilder\FilamentTranscribe\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Visualbuilder\FilamentTranscribe\Enums\TranscriptStatus;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages\EditTranscript;
use Visualbuilder\FilamentTranscribe\Jobs\TranscribeAudioJob;
use Visualbuilder\FilamentTranscribe\Models\Transcript;

class TranscribeAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        self::applyConfiguration($this);
    }

    public static function applyConfiguration($action)
    {

        $action
            ->name('transcribe_audio')
            ->label(new HtmlString('<u>T</u>ranscribe Audio'))
            ->keyBindings(['alt+t'])
            ->icon('heroicon-m-microphone')
            ->action(function (Transcript $record,EditTranscript $livewire) {
                    $livewire->showProgress = true;
                    $record->update(['status'=>TranscriptStatus::PENDING]);

                    TranscribeAudioJob::dispatch($record);
                    Notification::make()
                        ->title('Audio has been submitted for transcribing')
                        ->success()
                        ->send();

            });
    }
}
