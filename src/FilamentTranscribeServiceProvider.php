<?php

namespace Visualbuilder\FilamentTranscribe;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Visualbuilder\FilamentTranscribe\Commands\InstallCommand;

class FilamentTranscribeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-transcribe')
            ->hasConfigFile()
            ->hasViews('filament-transcribe')
            ->hasMigration('create_filament_transcribe_table')
            ->runsMigrations()
            ->hasCommand(InstallCommand::class);
    }


    public function packageRegistered(): void
    {
        parent::packageRegistered();

        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang/');

        $this->loadCss();

    }

    /**
     * Todo - can it be added via vite instead rather than loading another file
     * @return void
     */
    public function loadCss()
    {
        $publishedPath = public_path('vendor/filament-transcribe/filament-transcribe.css');

        $cssPath = file_exists($publishedPath)
            ? asset('vendor/filament-transcribe/filament-transcribe.css')
            : __DIR__ . '/../resources/css/style.css';

        FilamentAsset::register([
            Css::make('filament-transcribe', $cssPath),
        ], package: $this->getAssetPackageName());
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        if($this->app->runningInConsole()) {
            $this->publishAssets();
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'vb-transcribe');
        $this->loadRoutesFrom(__DIR__.'/../routes/filament-transcribe.php');
    }


    public function publishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../resources/css/style.css' => public_path('vendor/filament-transcribe/filament-transcribe.css'),
        ], 'filament-transcribe-assets');
    }

    protected function getAssetPackageName(): ?string
    {
        return 'visualbuilder/filament-transcribe';
    }



}
