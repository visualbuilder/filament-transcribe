<?php

use Filament\Pages\SubNavigationPosition;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User;
use Mockery;
use Visualbuilder\FilamentTranscribe\Filament\Forms\Components\AudioPlayer;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;
use Visualbuilder\FilamentTranscribe\Models\Transcript;

it('uses the Transcript model', function () {
    expect(TranscriptResource::getModel())->toBe(Transcript::class);
});

it('provides create transcript fields', function () {
    $fields = TranscriptResource::createTranscriptFields();

    expect($fields)->toHaveCount(2)
        ->and($fields[0])->toBeInstanceOf(SpatieMediaLibraryFileUpload::class)
        ->and($fields[1])->toBeInstanceOf(Toggle::class);
});

it('provides edit transcript fields', function () {
    $user = new User();
    $user->id = 1;
    $user->email = 'test@example.com';
    $user->name = 'Test User';
    $user->exists = true;
    Auth::login($user);

    $form = Mockery::mock(\Filament\Forms\Form::class);
    $record = new Transcript();
    $record->id = 1;
    $record->exists = true;
    $form->shouldReceive('getRecord')->andReturn($record);

    $fields = TranscriptResource::editTranscriptFields($form);

    expect($fields)->toHaveCount(5)
        ->and($fields[0])->toBeInstanceOf(AudioPlayer::class);
});

it('returns configured cluster', function () {
    config(['filament-transcribe.navigation.cluster' => 'test-cluster']);

    expect(TranscriptResource::getCluster())->toBe('test-cluster');
});

it('returns configured sub navigation position', function () {
    config(['filament-transcribe.navigation.subnav_position' => SubNavigationPosition::End]);

    expect(TranscriptResource::getSubNavigationPosition())->toBe(SubNavigationPosition::End);
});

it('defines resource pages', function () {
    $pages = TranscriptResource::getPages();

    expect($pages)->toHaveKeys(['index', 'record', 'edit']);
});
