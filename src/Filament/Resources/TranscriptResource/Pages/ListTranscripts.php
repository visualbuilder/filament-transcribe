<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;


use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\View;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;
use Visualbuilder\FilamentTranscribe\Models\Transcript;

class ListTranscripts extends ListRecords
{
    protected static string $resource = TranscriptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::FiveExtraLarge)
                ->modalSubmitActionLabel('Save and transcribe audio')
                ->form(TranscriptResource::createTranscriptFields())
                ->successNotificationTitle('Audio Uploaded Successfully')
                ->modalFooterActionsAlignment(Alignment::End)
                ->successRedirectUrl(fn (Transcript $record): string => TranscriptResource::getUrl('edit', ['record'=>$record]))
                ->createAnother(false),
            Actions\Action::make('record_audio')
                ->label('Record Audio')
                ->icon('heroicon-o-microphone')
                ->form([
                    \Filament\Forms\Components\View::make('filament-transcribe::components.audio_recorder'),
                ])
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalWidth(MaxWidth::FiveExtraLarge),
        ];
    }
}
