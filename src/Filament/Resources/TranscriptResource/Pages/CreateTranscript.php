<?php

namespace App\Filament\Resources\TranscriptResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;
use Visualbuilder\FilamentTranscribe\Jobs\TranscribeAudioJob;

class CreateTranscript extends CreateRecord
{
    protected static string $resource = TranscriptResource::class;

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label(__('filament-panels::resources/pages/create-record.form.actions.create.label'))
            ->submit('create')
            ->keyBindings(['mod+s']);
    }


}
