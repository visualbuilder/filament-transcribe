<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Visualbuilder\FilamentTinyEditor\TinyEditor;
use Visualbuilder\FilamentTranscribe\Enums\TranscriptStatus;
use Visualbuilder\FilamentTranscribe\Filament\Actions\StatusBadge;
use Visualbuilder\FilamentTranscribe\Filament\Fields\AudioUploadField;
use Visualbuilder\FilamentTranscribe\Filament\Forms\Components\RecordAudio;
use Visualbuilder\FilamentTranscribe\Filament\Fields\OwnerMorphSelectField;
use Visualbuilder\FilamentTranscribe\Filament\Forms\Components\AudioPlayer;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;
use Visualbuilder\FilamentTranscribe\Models\Transcript;

class TranscriptResource extends Resource
{
    protected static ?string $model = Transcript::class;

    protected bool $showProgress = false;

    public static function shouldRegisterNavigation(): bool
    {
        return filament('filament-transcribe')->shouldRegisterNavigation();
    }

    public static function getNavigationLabel(): string
    {
        return filament('filament-transcribe')->getNavigationLabel();
    }

    public static function getNavigationIcon(): ?string
    {
        return filament('filament-transcribe')->getNavigationIcon();
    }

    public static function getNavigationGroup(): ?string
    {
        return filament('filament-transcribe')->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return filament('filament-transcribe')->getNavigationSort();
    }

    /**
     * Note this must be set in the config - appears to be a filament bug -> plugin is not initialised when filament('filament-transcribe') called
     * @return string|null
     */
    public static function getCluster(): ?string
    {
        return config('filament-transcribe.navigation.cluster');
    }

    /**
     * Another bug filament('filament-transcribe')->getNavigationSubnavPosition() does not get called
     * Using config value works though
     * @return SubNavigationPosition
     */
    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return config('filament-transcribe.navigation.subnav_position');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Section::make('Transcript')
                    ->headerActions([
                        StatusBadge::make('status')
                    ])
                    ->schema(self::createTranscriptFields() + self::editTranscriptFields($form)),

            ]);
    }

    public static function createTranscriptFields(): array
    {
        return [
            Select::make('audio_mode')
                ->label('Select Input Method')
                ->options([
                    'upload' => 'Upload File',
                    'record' => 'Record Audio',
                ])
                ->default('upload')
                ->live(),
            AudioUploadField::make()
                ->visible(fn(Get $get) => $get('audio_mode') === 'upload'),
            Select::make('recording_device')
                ->label('Audio Source')
                ->options([])
                ->native(false)
                ->visible(fn(Get $get) => $get('audio_mode') === 'record'),
            RecordAudio::make('recording')
                ->deviceField('recording_device')
                ->visible(fn(Get $get) => $get('audio_mode') === 'record'),
            Toggle::make('redact_pii')
                ->default(true)
                ->label('Redact Personally Identifiable Information  (Forces en_US instead of en_GB)')
                ->hint('Removes names, email, address, bank details, phone numbers '),
        ];
    }

    public static function editTranscriptFields($form): array
    {
        return [
            AudioPlayer::make(),
            Grid::make()
                ->columns(2)
                ->schema([
                    OwnerMorphSelectField::make(),
//                    Fieldset::make('Speaker Names')
//                        ->columnSpan(1)
//                        ->columns(1)
//                        ->schema([
                            Repeater::make('speaker_names')
                                ->label(null)
                                ->simple(
                                    TextInput::make('name')
                                        ->label(null)
                                        ->required(),
                                )
                                ->default([
                                    'Speaker 1',
                                    'Speaker 2',
                                ])
                                ->minItems(1)
                                ->maxItems(30)
                                ->columnSpan(1)
                        ]),

//                ]),

            Placeholder::make('progress')
                ->label(false)
                ->hiddenOn('create')
                ->content(new HtmlString("<div class='flex items-center justify-center min-h-[100px]'><div class='block-loader'></div><span class='ml-4'>Transcribing in progress</span></div>"))
                ->visible(fn($livewire) => $livewire->showProgress ?? false),

            TinyEditor::make('transcribed_html')
                ->label('Transcription')
                ->live()
                ->hiddenOn('create')
                ->placeholder('This will be automatically filled by a background process.')
                ->visible(fn(Get $get) => $get('audio'))
                ->nullable(),

            //Create echo listener for broadcast event notifying that the transcript is complete
            View::make('filament-transcribe::components.transcript_echo')
                ->hiddenOn('create')
                ->viewData(['transcriptId' => $form->getRecord()?->id])
        ];
    }


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('duration_seconds')
                    ->label('Duration H:m:s')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        $totalSeconds = $state ?? 0;
                        $hours = floor($totalSeconds / 3600);
                        $minutes = floor(($totalSeconds % 3600) / 60);
                        $seconds = $totalSeconds % 60;
                        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    }),
                Tables\Columns\TextColumn::make('transcribed_text')
                    ->label('Transcript')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn($state) => TranscriptStatus::from($state->value)->getLabel())
                    ->color(fn($state) => TranscriptStatus::from($state->value)->getColor())
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTranscripts::route('/'),
            'edit'  => Pages\EditTranscript::route('/{record}/edit'),
        ];
    }
}
