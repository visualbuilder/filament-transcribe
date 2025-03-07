<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Fields;

use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Get;
use Filament\Forms\Set;

class OwnerMorphSelectField
{
    /**
     * Create a MorphToSelect field for selecting an owner.
     *
     * @param  string  $fieldName
     * @param  bool  $native
     * @param  bool  $searchable
     *
     * @return MorphToSelect
     */
    public static function make(string $fieldName = 'owner', bool $native = false, bool $searchable = true): MorphToSelect
    {
        // Retrieve user models from the config.
        $userModels = config('filament-transcribe.user_models', [
            [
                'model'           => config('auth.providers.users.model'),
                'title_attribute' => 'email',
            ],
        ]);

        $defaultUserId = auth()->id();
        $defaultType = auth()->user()::class;

        $types = [];
        foreach ($userModels as $userModel) {
            if (isset($userModel['model'], $userModel['title_attribute'])) {
                $types[] = MorphToSelect\Type::make($userModel['model'])
                    ->titleAttribute($userModel['title_attribute']);
            }
        }

        return MorphToSelect::make($fieldName)
            ->label('Owner')
            ->types($types)
            ->native($native)
            ->required()
            ->columnSpan(1)
            ->live()
            ->searchable($searchable)
            ->afterStateHydrated(function (Set $set, Get $get) use ($fieldName, $defaultType, $defaultUserId) {
                if (!$get("{$fieldName}_type")) {
                    $set("{$fieldName}_type", $defaultType);
                }
                if (!$get("{$fieldName}_id")) {
                    $set("{$fieldName}_id", $defaultUserId);
                }
            });
    }
}
