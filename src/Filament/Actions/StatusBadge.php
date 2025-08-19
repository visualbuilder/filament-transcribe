<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Actions;


use Filament\Actions\Action;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class StatusBadge extends Action
{
    protected function setUp(): void
    {
        parent::setUp();
        self::applyConfiguration($this);
    }

    public static function applyConfiguration(Action $action)
    {
        return $action->visible(fn(?Model $record) => $record?->id)
            ->label(fn(?Model $record): string => $record->id ? $record->status->getLabel() : '')
            ->size(Size::Large)
            ->badge()
            ->color(fn($record) => $record->id ? $record->status->getColor() : '')
            ->disabled();
    }
}
