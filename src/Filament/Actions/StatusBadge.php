<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Actions;

use Filament\Forms\Components\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\Model;

class StatusBadge extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        self::applyConfiguration($this);
    }

    public static function applyConfiguration($action)
    {
        return $action->visible(fn(?Model $record) => $record?->id)
            ->label(fn(?Model $record): string => $record->id ? $record->status->getLabel() : '')
            ->size(ActionSize::ExtraLarge)
            ->badge()
            ->color(fn($record) => $record->id ? $record->status->getColor() : '')
            ->disabled();
    }
}
